<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CalculatorsController extends Controller
{
    /**
     * Display the calculators landing page.
     */
    public function index(): View
    {
        return view('calculators.index');
    }

    /**
     * Diagnostics calculator placeholder.
     */
    public function diagnostics(): View
    {
        return view('calculators.diagnostics');
    }

    /**
     * Prognosis calculator placeholder.
     */
    public function prognosis(): View
    {
        return view('calculators.prognosis');
    }
}
