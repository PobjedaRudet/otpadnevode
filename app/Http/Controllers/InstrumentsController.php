<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\Company;

class InstrumentsController extends Controller
{
    public function index()
    {
        // Eager load instruments for each company to avoid N+1 queries
        $companies = Company::with(['instruments' => function($q){
            $q->orderBy('name');
        }])->orderBy('name')->get();

        // Flat collection (if needed elsewhere) still available
        $totalInstruments = $companies->sum(fn($c) => $c->instruments->count());

        return view('instruments.index', compact('companies', 'totalInstruments'));
    }
}
