<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class TherapyController extends Controller
{
    /**
     * Ensure schema exists by checking core tables
     */
    private function ensureSchema(): void
    {
        try {
            $hasArticles = DB::select("SHOW TABLES LIKE 'articles'");
            $hasStudies = DB::select("SHOW TABLES LIKE 'studies'");

            if (!$hasArticles || !$hasStudies) {
                $schemaPath = database_path('schema.sql');
                if (!is_readable($schemaPath)) {
                    throw new \RuntimeException('schema.sql not found or not readable.');
                }
                $sql = file_get_contents($schemaPath);
                DB::unprepared($sql);
            }
            
            // Ensure supplementary columns/tables exist
            $this->ensureArticlesPECOColumns();
            $this->ensureIndividualizationsTable();
            $this->ensureIndividualizationsColumns();
        } catch (\Throwable $e) {
            abort(500, "Schema check/creation failed: " . $e->getMessage());
        }
    }
    
    /**
     * Ensure articles table has PECO columns
     */
    private function ensureArticlesPECOColumns(): void
    {
        static $done = false;
        if ($done) return;
        $done = true;
        
        try {
            $needed = [
                'population_pico' => "TEXT NULL",
                'exposure_pico' => "VARCHAR(255) NULL",
                'comparator_pico' => "VARCHAR(255) NULL",
                'outcome_pico' => "VARCHAR(255) NULL",
            ];
            
            $existing = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'articles'");
            $have = array_map(function ($r) {
                return strtolower($r->COLUMN_NAME);
            }, $existing);
            
            foreach ($needed as $col => $def) {
                if (!in_array(strtolower($col), $have)) {
                    DB::statement("ALTER TABLE articles ADD COLUMN $col $def");
                }
            }
        } catch (\Throwable $e) {
            // Non-fatal
        }
    }

    /**
     * Ensure individualizations table has expected columns (for older imports)
     */
    private function ensureIndividualizationsColumns(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        try {
            $needed = [
                'baseline_risk' => "DECIMAL(8,6) NULL",
                'rr_snapshot' => "DECIMAL(12,6) NULL",
                'treated_risk_ind' => "DECIMAL(10,6) NULL",
                'arr_ind' => "DECIMAL(12,6) NULL",
                'nnt_ind' => "INT NULL",
                'scenario_age' => "INT NULL",
                'scenario_sex' => "VARCHAR(16) NULL",
                'scenario_comorbidities' => "TEXT NULL",
                'scenario_setting' => "VARCHAR(255) NULL",
                'scenario_notes' => "TEXT NULL",
            ];

            $existing = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'individualizations'");
            $have = array_map(function ($r) {
                return strtolower($r->COLUMN_NAME);
            }, $existing);

            foreach ($needed as $col => $definition) {
                if (!in_array(strtolower($col), $have)) {
                    DB::statement("ALTER TABLE individualizations ADD COLUMN $col $definition");
                }
            }
        } catch (\Throwable $e) {
            // Non-fatal, ignore
        }
    }

    /**
     * Ensure the individualizations table exists
     */
    private function ensureIndividualizationsTable(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        try {
            DB::statement(<<<SQL
                CREATE TABLE IF NOT EXISTS individualizations (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    study_id INT UNSIGNED NOT NULL,
                    baseline_risk DECIMAL(8,6) NULL,
                    rr_snapshot DECIMAL(12,6) NULL,
                    treated_risk_ind DECIMAL(10,6) NULL,
                    arr_ind DECIMAL(12,6) NULL,
                    nnt_ind INT NULL,
                    scenario_age INT NULL,
                    scenario_sex VARCHAR(16) NULL,
                    scenario_comorbidities TEXT NULL,
                    scenario_setting VARCHAR(255) NULL,
                    scenario_notes TEXT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY idx_individualizations_study (study_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);
        } catch (\Throwable $e) {
            // Non-fatal
        }
    }

    private function formatAuthors(?string $authorsJson): string
    {
        if (!$authorsJson) {
            return '—';
        }

        $authors = json_decode($authorsJson, true);
        if (!is_array($authors)) {
            return '—';
        }

        $names = [];
        foreach ($authors as $author) {
            $given = trim($author['given'] ?? '');
            $family = trim($author['family'] ?? '');
            if ($family === '' && $given === '') {
                continue;
            }

            $initials = '';
            if ($given !== '') {
                $parts = preg_split('/\s+/', $given);
                foreach ($parts as $part) {
                    if ($part !== '') {
                        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
                    }
                }
            }

            $names[] = trim($family . ($initials !== '' ? ' ' . $initials : ''));
        }

        if (empty($names)) {
            return '—';
        }

        if (count($names) > 10) {
            return implode(', ', array_slice($names, 0, 10)) . ' et al.';
        }

        return implode(', ', $names);
    }

    /**
     * Show the article/study entry form
     */
    public function articleForm()
    {
        $this->ensureSchema();
        return view('therapy.article_form');
    }

    /**
     * Show list of studies
     */
    public function studiesList(Request $request)
    {
        $this->ensureSchema();
        
        $q = trim($request->get('q', ''));

        $indCounts = DB::table('individualizations')
            ->select('study_id', DB::raw('COUNT(*) AS ind_count'), DB::raw('MAX(created_at) AS last_ind_created'))
            ->groupBy('study_id');

        $query = DB::table('studies as s')
            ->join('articles as a', 'a.id', '=', 's.article_id')
            ->leftJoinSub($indCounts, 'ind', function ($join) {
                $join->on('ind.study_id', '=', 's.id');
            })
            ->select([
                's.id',
                's.rr',
                's.arr',
                's.nnt',
                's.nnh',
                's.valid_rand',
                's.valid_conceal',
                's.valid_blind',
                's.valid_itt',
                's.valid_follow',
                DB::raw('COALESCE(ind.ind_count, 0) AS ind_count'),
                DB::raw('ind.last_ind_created'),
                'a.exposure_pico',
                'a.comparator_pico',
                'a.outcome_pico',
                'a.population_pico',
                'a.doi',
            ])
            ->orderByDesc('s.created_at')
            ->limit(300);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('a.exposure_pico', 'like', "%{$q}%")
                    ->orWhere('a.outcome_pico', 'like', "%{$q}%")
                    ->orWhere('a.population_pico', 'like', "%{$q}%");
            });
        }

        $studies = $query->get();

        return view('therapy.studies_list', [
            'studies' => $studies,
            'searchQuery' => $q
        ]);
    }

    /**
     * Show individualization list for a study
     */
    public function indList($studyId)
    {
        $this->ensureSchema();
        
        $study = DB::table('studies as s')
            ->join('articles as a', 'a.id', '=', 's.article_id')
            ->select(
                's.*',
                'a.article_title',
                'a.journal_title',
                'a.doi',
                'a.population_pico',
                'a.exposure_pico',
                'a.comparator_pico',
                'a.outcome_pico'
            )
            ->where('s.id', $studyId)
            ->first();
        if (!$study) {
            abort(404, 'Study not found');
        }

        $individualizations = DB::table('individualizations')
            ->where('study_id', $studyId)
            ->orderByDesc('created_at')
            ->get();

        return view('therapy.ind_list', [
            'study' => $study,
            'individualizations' => $individualizations
        ]);
    }

    /**
     * Show individualization creation form
     */
    public function indCreate($studyId)
    {
        $this->ensureSchema();

        $study = DB::table('studies as s')
            ->join('articles as a', 'a.id', '=', 's.article_id')
            ->select(
                's.*',
                'a.article_title',
                'a.journal_title',
                'a.doi',
                'a.population_pico',
                'a.exposure_pico',
                'a.comparator_pico',
                'a.outcome_pico',
                'a.authors_json'
            )
            ->where('s.id', $studyId)
            ->first();

        if (!$study) {
            abort(404, 'Study not found');
        }

        $latest = DB::table('individualizations')
            ->where('study_id', $studyId)
            ->orderByDesc('created_at')
            ->first();

        return view('therapy.ind_create', [
            'study' => $study,
            'authors' => $this->formatAuthors($study->authors_json ?? null),
            'latest' => $latest,
        ]);
    }

    /**
     * Store individualization entry
     */
    public function indStore(Request $request, $studyId)
    {
        $this->ensureSchema();

        $study = DB::table('studies')->where('id', $studyId)->first();
        if (!$study) {
            abort(404, 'Study not found');
        }

        $data = $request->validate([
            'baseline_risk' => ['nullable', 'numeric', 'between:0,1'],
            'scenario_age' => ['nullable', 'integer', 'min:0'],
            'scenario_sex' => ['nullable', 'string', 'max:16'],
            'scenario_comorbidities' => ['nullable', 'string'],
            'scenario_setting' => ['nullable', 'string', 'max:255'],
            'scenario_notes' => ['nullable', 'string'],
        ]);

        $baseline = $data['baseline_risk'] ?? null;
        $rr = $study->rr !== null ? (float) $study->rr : null;
        $treated = null;
        $arrInd = null;
        $nntInd = null;

        if ($baseline !== null && $rr !== null) {
            $treated = max(0, min(1, $baseline * $rr));
            $arrInd = $baseline - $treated;
            if ($arrInd != 0) {
                $nntInd = (int) ceil(1 / abs($arrInd));
            }
        }

        DB::table('individualizations')->insert([
            'study_id' => $studyId,
            'baseline_risk' => $baseline,
            'rr_snapshot' => $rr,
            'treated_risk_ind' => $treated,
            'arr_ind' => $arrInd,
            'nnt_ind' => $nntInd,
            'scenario_age' => $data['scenario_age'] ?? null,
            'scenario_sex' => $data['scenario_sex'] ?? null,
            'scenario_comorbidities' => $data['scenario_comorbidities'] ?? null,
            'scenario_setting' => $data['scenario_setting'] ?? null,
            'scenario_notes' => $data['scenario_notes'] ?? null,
            'created_at' => now(),
        ]);

        return redirect()->route('therapy.ind.list', $studyId)->with('success', 'Individualization saved.');
    }

    /**
     * Show individualization results
     */
    public function indResults($indId)
    {
        $this->ensureSchema();
        
        $ind = DB::table('individualizations')->where('id', $indId)->first();
        if (!$ind) {
            abort(404, 'Individualization not found');
        }

        $study = DB::table('studies')->where('id', $ind->study_id)->first();

        return view('therapy.ind_results', [
            'ind' => $ind,
            'study' => $study
        ]);
    }

    /**
     * Show reading journal form (step between article_form and compute_results)
     */
    public function readingJournalForm(Request $request)
    {
        $this->ensureSchema();
        
        // Pull article metadata
        $article_id = $request->input('article_id');
        $doi = trim($request->input('doi', ''));
        
        $article = null;
        if ($article_id) {
            $article = DB::table('articles')->where('id', $article_id)->first();
        } elseif ($doi !== '') {
            $article = DB::table('articles')->where('doi', $doi)->first();
        }
        
        // Update PECO if provided
        $p_pop = trim($request->input('population_pico', ''));
        $p_exp = trim($request->input('exposure_pico', ''));
        $p_cmp = trim($request->input('comparator_pico', ''));
        $p_out = trim($request->input('outcome_pico', ''));
        
        if ($article) {
            $updates = [];
            if ($p_pop !== '') $updates['population_pico'] = $p_pop;
            if ($p_exp !== '') $updates['exposure_pico'] = $p_exp;
            if ($p_cmp !== '') $updates['comparator_pico'] = $p_cmp;
            if ($p_out !== '') $updates['outcome_pico'] = $p_out;
            if ($updates) {
                DB::table('articles')->where('id', $article->id)->update($updates);
                $article = DB::table('articles')->where('id', $article->id)->first();
            }
        }
        
        $articleArray = $article ? (array)$article : [];
        
        return view('therapy.reading_journal_form', [
            'article' => $articleArray,
            'doi' => $doi,
            'title' => $articleArray['article_title'] ?? '',
            'journal' => $articleArray['journal_title'] ?? '',
            'pub_year' => $articleArray['pub_year'] ?? '',
            'publisher' => $articleArray['publisher'] ?? '',
            'population_pico' => $articleArray['population_pico'] ?? '',
            'exposure_pico' => $articleArray['exposure_pico'] ?? '',
            'comparator_pico' => $articleArray['comparator_pico'] ?? '',
            'outcome_pico' => $articleArray['outcome_pico'] ?? '',
        ]);
    }

    /**
     * Show reading journal (list view)
     */
    public function readingJournal()
    {
        $this->ensureSchema();
        
        $studies = DB::table('studies as s')
            ->join('articles as a', 'a.id', '=', 's.article_id')
            ->select([
                's.id',
                's.created_at',
                'a.exposure_pico',
                'a.comparator_pico',
                'a.outcome_pico',
                'a.doi',
                's.rr',
                's.arr',
                's.nnt',
            ])
            ->orderByDesc('s.created_at')
            ->limit(100)
            ->get();

        return view('therapy.reading_journal', ['studies' => $studies]);
    }

    /**
     * Delete a study
     */
    public function deleteStudy(Request $request, $studyId)
    {
        $this->ensureSchema();
        
        // Validate CSRF token
        $request->validate([]);

        DB::beginTransaction();
        try {
            // Delete related individualizations first
            DB::table('individualizations')->where('study_id', $studyId)->delete();
            
            // Get article_id before deleting study
            $study = DB::table('studies')->where('id', $studyId)->first();
            if (!$study) {
                abort(404, 'Study not found');
            }

            $articleId = $study->article_id;

            // Delete the study
            DB::table('studies')->where('id', $studyId)->delete();

            // Check if article has other studies
            $otherStudies = DB::table('studies')->where('article_id', $articleId)->count();
            if ($otherStudies === 0) {
                // Delete the article if no other studies reference it
                DB::table('articles')->where('id', $articleId)->delete();
            }

            DB::commit();
            return redirect()->route('therapy.studies.list')->with('success', 'Study deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('therapy.studies.list')->with('error', 'Failed to delete study: ' . $e->getMessage());
        }
    }

    /**
     * Compute results from reading journal form
     */
    public function computeResults(Request $request)
    {
        $this->ensureSchema();
        $this->ensureStudiesColumns();
        
        $errors = [];
        
        // Parse inputs
        $article_id = $request->input('article_id');
        $doi = trim($request->input('doi', ''));
        
        $exposure_label = trim($request->input('exposure_pico', '')) ?: 'Exposure';
        $comparator_label = trim($request->input('comparator_pico', '')) ?: 'Comparator';
        $outcome_label = trim($request->input('outcome_pico', '')) ?: 'Outcome';
        
        // Validity checklist
        $valid_checks = $request->input('valid', []);
        $valid_checks = is_array($valid_checks) ? array_keys($valid_checks) : [];
        $valid_notes = $request->input('valid_remarks', []);
        
        // 2x2 table inputs
        $A = $request->input('A') !== null && $request->input('A') !== '' ? (int)$request->input('A') : null;
        $B = $request->input('B') !== null && $request->input('B') !== '' ? (int)$request->input('B') : null;
        $C = $request->input('C') !== null && $request->input('C') !== '' ? (int)$request->input('C') : null;
        $D = $request->input('D') !== null && $request->input('D') !== '' ? (int)$request->input('D') : null;
        $N1 = $request->input('N1') !== null && $request->input('N1') !== '' ? (int)$request->input('N1') : null;
        $N0 = $request->input('N0') !== null && $request->input('N0') !== '' ? (int)$request->input('N0') : null;
        
        // Validation: A and C are required
        if ($A === null) $errors[] = "A (events in exposed) is required.";
        if ($C === null) $errors[] = "C (events in unexposed) is required.";
        
        // Must provide either (B and D) OR (N1 and N0)
        $hasBD = ($B !== null && $D !== null);
        $hasN1N0 = ($N1 !== null && $N0 !== null);
        
        if (!$hasBD && !$hasN1N0) {
            $errors[] = "Provide either (B & D) or (N1 & N0) along with A & C.";
        }
        
        if ($hasBD && $hasN1N0) {
            $errors[] = "Provide either (B & D) or (N1 & N0), not both. System will derive the rest.";
        }
        
        // Derive missing values
        if ($hasBD && !$hasN1N0) {
            // User provided A, C, B, D → calculate N1, N0
            $N1 = $A + $B;
            $N0 = $C + $D;
        } elseif ($hasN1N0 && !$hasBD) {
            // User provided A, C, N1, N0 → calculate B, D
            $B = $N1 - $A;
            $D = $N0 - $C;
            
            // Validate derived values are non-negative
            if ($B < 0) $errors[] = "Derived B (N1 - A) is negative. Check your values.";
            if ($D < 0) $errors[] = "Derived D (N0 - C) is negative. Check your values.";
        }
        
        // Final validation after derivation
        if (!$errors) {
            if ($A > $N1) $errors[] = "A cannot exceed N1.";
            if ($C > $N0) $errors[] = "C cannot exceed N0.";
            if (($A + $B) !== $N1) $errors[] = "A + B must equal N1.";
            if (($C + $D) !== $N0) $errors[] = "C + D must equal N0.";
        }
        
        if ($errors) {
            return back()->withErrors($errors)->withInput();
        }
        
        // Compute metrics
        $Re = $this->safeDiv($A, $N1);
        $Ru = $this->safeDiv($C, $N0);
        $RR = ($Re !== null && $Ru !== null && $Ru > 0) ? ($Re / $Ru) : null;
        $ARR = ($Re !== null && $Ru !== null) ? ($Ru - $Re) : null;
        $NNT = null;
        $NNH = null;
        if ($ARR !== null && $ARR != 0) {
            $x = ceil(1 / abs($ARR));
            if ($ARR > 0) $NNT = (int)$x; else $NNH = (int)$x;
        }
        
        // Get article_id if not provided
        if (!$article_id && $doi !== '') {
            $article_id = DB::table('articles')->where('doi', $doi)->value('id');
        }

        if (!$article_id) {
            return back()->withErrors(['article_id' => 'Unable to determine article. Please fetch DOI again.'])->withInput();
        }
        
        // Get labels from article or use defaults
        $article = DB::table('articles')->where('id', $article_id)->first();
        $treatment = $exposure_label ?: 'Treatment';
        $control = $comparator_label ?: 'Control';
        $outcome = $outcome_label ?: 'Outcome';
        $population = '';
        
        if ($article) {
            $treatment = $article->exposure_pico ?: $treatment;
            $control = $article->comparator_pico ?: $control;
            $outcome = $article->outcome_pico ?: $outcome;
            $population = $article->population_pico ?: '';
        }

        // Save study
        $studyId = DB::table('studies')->insertGetId([
            'article_id' => $article_id,
            'treatment' => $treatment,
            'control' => $control,
            'outcome' => $outcome,
            'A_exposed_yes' => $A,
            'B_exposed_no' => $B,
            'C_unexposed_yes' => $C,
            'D_unexposed_no' => $D,
            'N1_exposed_total' => $N1,
            'N0_unexposed_total' => $N0,
            'risk_exposed' => $Re,
            'risk_unexposed' => $Ru,
            'rr' => $RR,
            'arr' => $ARR,
            'nnt' => $NNT,
            'nnh' => $NNH,
            'valid_rand' => in_array('rand', $valid_checks) ? 1 : 0,
            'valid_conceal' => in_array('conceal', $valid_checks) ? 1 : 0,
            'valid_blind' => in_array('blind', $valid_checks) ? 1 : 0,
            'valid_itt' => in_array('itt', $valid_checks) ? 1 : 0,
            'valid_follow' => in_array('follow', $valid_checks) ? 1 : 0,
            'valid_rand_note' => $valid_notes['rand'] ?? null,
            'valid_conceal_note' => $valid_notes['conceal'] ?? null,
            'valid_blind_note' => $valid_notes['blind'] ?? null,
            'valid_itt_note' => $valid_notes['itt'] ?? null,
            'valid_follow_note' => $valid_notes['follow'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studyRecord = DB::table('studies')->where('id', $studyId)->first();

        $validLabels = [
            'rand' => 'Random allocation',
            'conceal' => 'Allocation concealment',
            'blind' => 'Blinding',
            'itt' => 'Intention-to-treat',
            'follow' => 'Follow-up completeness',
        ];
        $validCount = 0;
        foreach (array_keys($validLabels) as $key) {
            if (in_array($key, $valid_checks)) {
                $validCount++;
            }
        }
        $allChecked = $validCount === count($validLabels);
        $validMessage = $allChecked ? 'The study is valid.' : 'Interpret the results cautiously.';

        $interpretation = 'No absolute risk difference detected between ' . $treatment . ' and ' . $control . ' for ' . $outcome . '.';
        if ($ARR === null) {
            $interpretation = 'Insufficient data to compute interpretation.';
        } elseif ($ARR > 0 && $NNT !== null) {
            $interpretation = "For every {$NNT} treated with {$treatment}, you can prevent one {$outcome}.";
        } elseif ($ARR < 0 && $NNH !== null) {
            $interpretation = "Caution: For every {$NNH} treated with {$treatment}, one additional {$outcome} may occur (harm).";
        }

        $authorsJson = $article->authors_json ?? null;

        return view('therapy.compute_results', [
            'studyId' => $studyId,
            'article' => $article,
            'articlePopulation' => $population,
            'authors' => $this->formatAuthors($authorsJson),
            'doi' => $article->doi ?? $doi,
            'treatment' => $treatment,
            'control' => $control,
            'outcome' => $outcome,
            'counts' => [
                'A' => $A,
                'B' => $B,
                'C' => $C,
                'D' => $D,
                'N1' => $N1,
                'N0' => $N0,
            ],
            'metrics' => [
                'Re' => $Re,
                'Ru' => $Ru,
                'RR' => $RR,
                'ARR' => $ARR,
                'NNT' => $NNT,
                'NNH' => $NNH,
            ],
            'interpretation' => $interpretation,
            'validLabels' => $validLabels,
            'validChecks' => array_flip($valid_checks),
            'validNotes' => $valid_notes,
            'validCount' => $validCount,
            'validMessage' => $validMessage,
            'studyRecord' => $studyRecord,
        ]);
    }
    
    private function safeDiv($a, $b)
    {
        return ($b === 0 || $b === 0.0 || $b === null) ? null : ($a / $b);
    }
    
    private function ensureStudiesColumns()
    {
        static $done = false;
        if ($done) return;
        $done = true;
        
        try {
            $needed = [
                'valid_rand' => "TINYINT(1) NULL",
                'valid_conceal' => "TINYINT(1) NULL",
                'valid_blind' => "TINYINT(1) NULL",
                'valid_itt' => "TINYINT(1) NULL",
                'valid_follow' => "TINYINT(1) NULL",
                'valid_rand_note' => "TEXT NULL",
                'valid_conceal_note' => "TEXT NULL",
                'valid_blind_note' => "TEXT NULL",
                'valid_itt_note' => "TEXT NULL",
                'valid_follow_note' => "TEXT NULL",
            ];
            
            $existing = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'studies'");
            $have = array_map(function ($r) {
                return strtolower($r->COLUMN_NAME);
            }, $existing);
            
            foreach ($needed as $col => $def) {
                if (!in_array(strtolower($col), $have)) {
                    DB::statement("ALTER TABLE studies ADD COLUMN $col $def");
                }
            }
        } catch (\Throwable $e) {
            // Non-fatal
        }
    }

    /**
     * Handle DOI autofetch and save
     */
    public function doiAutofetchSave(Request $request)
    {
        $this->ensureSchema();
        
        $doi = trim($request->input('doi', ''));
        if ($doi === '') {
            return response()->json(['ok' => false, 'error' => 'Missing DOI'], 400);
        }

        // Normalize DOI
        $doi = preg_replace('~^https?://doi\.org/~i', '', $doi);

        // Fetch from Crossref
        $apiUrl = 'https://api.crossref.org/works/' . rawurlencode($doi);

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'User-Agent: EBM-Calculator/1.0 (mailto:your-email@example.com)'
            ],
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err || $code < 200 || $code >= 300) {
            return response()->json(['ok' => false, 'error' => 'Crossref request failed', 'detail' => $err ?: "HTTP $code"], 502);
        }

        $j = json_decode($resp, true);
        if (!isset($j['message'])) {
            return response()->json(['ok' => false, 'error' => 'Unexpected Crossref response'], 500);
        }
        $m = $j['message'];

        // Extract fields
        $title = $m['title'][0] ?? '';
        $journal = $m['container-title'][0] ?? '';
        $authors = [];
        if (!empty($m['author']) && is_array($m['author'])) {
            foreach ($m['author'] as $a) {
                $authors[] = [
                    'given' => $a['given'] ?? '',
                    'family' => $a['family'] ?? '',
                    'affiliation' => $a['affiliation'] ?? []
                ];
            }
        }
        $authors_json = json_encode($authors, JSON_UNESCAPED_UNICODE);

        $dateParts = $m['published-print']['date-parts'][0]
            ?? $m['published-online']['date-parts'][0]
            ?? $m['issued']['date-parts'][0]
            ?? null;
        [$y, $mo, $d] = [null, null, null];
        if (is_array($dateParts)) {
            $y = $dateParts[0] ?? null;
            $mo = $dateParts[1] ?? null;
            $d = $dateParts[2] ?? null;
        }

        $volume = $m['volume'] ?? null;
        $issue_no = $m['issue'] ?? null;
        $pages = $m['page'] ?? null;
        $publisher = $m['publisher'] ?? null;
        $url = $m['URL'] ?? null;
        $abstract_text = null;
        if (!empty($m['abstract'])) {
            $abstract_text = trim(strip_tags($m['abstract']));
        }

        // Upsert into database
        try {
            DB::statement("
                INSERT INTO articles
                (doi, article_title, journal_title, authors_json, pub_year, pub_month, pub_day, volume, issue_no, pages, publisher, url, abstract_text)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                article_title = VALUES(article_title),
                journal_title = VALUES(journal_title),
                authors_json = VALUES(authors_json),
                pub_year = VALUES(pub_year),
                pub_month = VALUES(pub_month),
                pub_day = VALUES(pub_day),
                volume = VALUES(volume),
                issue_no = VALUES(issue_no),
                pages = VALUES(pages),
                publisher = VALUES(publisher),
                url = VALUES(url),
                abstract_text = VALUES(abstract_text),
                updated_at = CURRENT_TIMESTAMP
            ", [$doi, $title, $journal, $authors_json, $y, $mo, $d, $volume, $issue_no, $pages, $publisher, $url, $abstract_text]);

            $article = DB::table('articles')->where('doi', $doi)->first();
            return response()->json(['ok' => true, 'article' => $article], 200);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => 'DB upsert failed', 'detail' => $e->getMessage()], 500);
        }
    }
}
