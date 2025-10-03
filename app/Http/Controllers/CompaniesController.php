<?php

namespace App\Http\Controllers;

use App\Models\Company;

class CompaniesController extends Controller
{
    public function index()
    {
        $companies = Company::orderBy('name')->paginate(15);
        return view('companies.index', compact('companies'));
    }
}
