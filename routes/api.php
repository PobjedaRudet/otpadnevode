<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Record;
use App\Models\Company;
use App\Models\Instrument;
use Illuminate\Support\Facades\Log;

Route::post('/records', function (Request $request) {
    Log::info('Incoming record payload', $request->all());

    $validated = $request->validate([
        // Accept either instrument_id directly OR (firma + mjrni_instrument)
        'instrument_id' => 'nullable|integer|exists:instruments,id',
        'firma' => 'required_without:instrument_id|string|max:255',
        'mjrni_instrument' => 'required_without:instrument_id|string|max:255',
        'vrijeme' => 'required|date',
        'datum' => 'required|date',
        'vrijednost' => 'required|numeric',
    ]);

    // Resolve instrument
    if (!empty($validated['instrument_id'])) {
        $instrument = Instrument::find($validated['instrument_id']);
    } else {
        $company = Company::firstOrCreate(['name' => $validated['firma']]);
        $instrument = Instrument::firstOrCreate([
            'company_id' => $company->id,
            'name' => $validated['mjrni_instrument'],
        ]);
    }

    $record = Record::create([
        'instrument_id' => $instrument->id,
        'vrijeme' => $validated['vrijeme'],
        'datum' => $validated['datum'] ,
        'vrijednost' => $validated['vrijednost'],
    ]);

    return response()->json([
        'message' => 'Zapis uspješno kreiran.',
        'data' => $record->load('instrument.company'),
    ], 201);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
