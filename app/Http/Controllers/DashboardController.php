<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Instrument;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Dohvati sve firme i instrumente (uz eager load firme radi performansi)
        $companies = Company::orderBy('name')->get();
        $instruments = Instrument::with('company')->orderBy('name')->get();

        return view('dashboard', compact('companies', 'instruments'));
    }
}
