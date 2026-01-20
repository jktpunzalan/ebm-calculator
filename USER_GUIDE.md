# SALIKSIC EBM Calculator - User Guide

## Quick Start

### Access the Application
Visit: **http://localhost:8000**

## Creating a New Study

### Step 1: Article Form
1. Go to **Home** → Click "➕ New Study"
2. Or visit: http://localhost:8000/therapy/article-form

### Step 2: Enter DOI (Optional but Recommended)
1. Enter the study DOI (e.g., `10.1056/NEJMoa1911303`)
2. Click **"Fetch from DOI"**
3. Article details will auto-populate:
   - Title
   - Journal
   - Publication Year
   - Publisher

### Step 3: Enter PECO
Fill in the Population, Exposure, Comparator, and Outcome:
- **Population**: e.g., "Adults with HFrEF NYHA II–IV"
- **Exposure**: e.g., "Dapagliflozin"
- **Comparator**: e.g., "Placebo"
- **Outcome**: e.g., "CV death or hospitalization for HF"

Click **"Compute"** to continue.

### Step 4: Reading Journal (Validity & Data Entry)
You'll be taken to the Reading Journal page where you:

#### A. Review Article Details
The article information you entered will be displayed.

#### B. Complete Validity Checklist
Check all that apply (Therapy studies):
- ☐ Random allocation of treatment?
- ☐ Allocation concealment adequate?
- ☐ Blinding of patients, clinicians, and outcome assessors?
- ☐ Intention-to-treat analysis used?
- ☐ Follow-up long enough and complete?

Add remarks in the text boxes if needed.

#### C. Enter 2×2 Table Data
You need to provide:
- **N1** (Total Exposed) - Required
- **N0** (Total Unexposed) - Required
- **Either** (A & C) **or** (B & D):
  - **A**: Events in exposed group
  - **C**: Events in unexposed group
  - **B**: No events in exposed group
  - **D**: No events in unexposed group

**Example:**
```
                 Outcome=Yes  Outcome=No   Total
Exposed (Rx)         75         925        1000 (N1)
Unexposed (Ctrl)    100         900        1000 (N0)
```

Enter: `N1=1000`, `N0=1000`, `A=75`, `C=100`

The system will automatically calculate B and D.

Click **"Continue to Compute"**

### Step 5: Results
- Study will be saved with computed metrics:
  - **RR** (Relative Risk)
  - **ARR** (Absolute Risk Reduction)
  - **NNT** (Number Needed to Treat)
- You'll be redirected to the Studies List

## Viewing Studies

### Studies List
Visit: http://localhost:8000/therapy/studies

Features:
- **Search** by Exposure or Outcome
- View all computed metrics
- See validity scores (X/5)
- **Individualize** button for each study
- **Delete** studies

### Reading Journal
Visit: http://localhost:8000/therapy/reading-journal

View chronological list of recent studies.

## Individualization

### View Individualizations
1. From Studies List, click **"Individualize"** next to a study
2. See all individualizations for that study
3. Click **"View Details"** to see:
   - Study metrics (RR, ARR, NNT)
   - Individualized ARR and NNT
   - Creation timestamp

## Tips & Tricks

### DOI Lookup
- Accepts DOIs in any format:
  - `10.1056/NEJMoa1911303`
  - `https://doi.org/10.1056/NEJMoa1911303`
- Fetches from Crossref API
- Saves author information, abstract, and more

### 2×2 Table Auto-Calculation
The system is smart about deriving missing values:
- If you enter **A** and **N1**, it calculates **B = N1 - A**
- If you enter **C** and **N0**, it calculates **D = N0 - C**
- Validates that totals match

### Validity Scoring
- Studies are scored out of 5 validity criteria
- 3+ valid criteria = Green badge ✓
- < 3 criteria = Yellow badge ⚠️

### Search
- Search works on Exposure and Outcome fields
- Case-insensitive
- Partial matching supported

## Keyboard Shortcuts

When on article form:
- **Tab** - Move between fields
- **Enter** on DOI field - Triggers fetch

## Troubleshooting

### DOI Fetch Fails
- **Check internet connection** - Requires access to api.crossref.org
- **Verify DOI is correct** - Should be in format: 10.XXXX/YYYYYYY
- **Try without https://doi.org/** prefix

### Study Won't Save
- Ensure **N1 and N0** are filled
- Provide at least **A & C** or **B & D**
- Check that values don't exceed totals (e.g., A ≤ N1)

### Can't Find Study
- Use **Search** function in Studies List
- Check **Reading Journal** for chronological view

## Calculations

### Risk Calculation
- **Re** (Risk Exposed) = A / N1
- **Ru** (Risk Unexposed) = C / N0

### Metrics
- **RR** (Relative Risk) = Re / Ru
- **ARR** (Absolute Risk Reduction) = Ru - Re
- **NNT** (Number Needed to Treat) = 1 / ARR (when ARR > 0)
- **NNH** (Number Needed to Harm) = 1 / |ARR| (when ARR < 0)

### Interpretation
- **Positive ARR**: Treatment beneficial
  - "For every NNT treated, you can prevent one outcome"
- **Negative ARR**: Treatment harmful
  - "Caution: For every NNH treated, one additional outcome may occur"
- **Zero ARR**: No difference between groups

## Data Safety

### What Gets Saved
- Article metadata (from DOI)
- PECO information
- 2×2 table values
- Computed metrics
- Validity checklist responses

### What Doesn't Get Saved
- Temporary form data (cleared on reset)
- Failed DOI lookups
- Draft entries (until you click "Continue to Compute")

## Support

For issues:
1. Check **TROUBLESHOOTING.md**
2. Review **SETUP.md** for configuration
3. See **FIXES_APPLIED.md** for latest changes

## Quick Reference

### URLs
- Home: `/`
- New Study: `/therapy/article-form`
- Studies List: `/therapy/studies`
- Reading Journal: `/therapy/reading-journal`

### File Locations
- Legacy backup: `_legacy_backup/`
- Database schema: `database/schema.sql`
- Laravel logs: `storage/logs/laravel.log`

## Example Workflow

1. **Navigate** to http://localhost:8000
2. Click **"➕ New Study"**
3. **Enter DOI**: `10.1056/NEJMoa1911303`
4. Click **"Fetch from DOI"** (wait ~2 seconds)
5. **Verify** article details populated
6. **Fill PECO**:
   - Population: "Adults with HFrEF"
   - Exposure: "Dapagliflozin"
   - Comparator: "Placebo"
   - Outcome: "CV death or HF hospitalization"
7. Click **"Compute"**
8. **Check all 5** validity boxes (if study is valid)
9. **Enter 2×2 data**:
   - N1: 2373
   - N0: 2371
   - A: 386
   - C: 502
10. Click **"Continue to Compute"**
11. **View results** in Studies List

Study saved! ✅
