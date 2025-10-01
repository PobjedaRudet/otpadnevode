<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Record;
use Illuminate\Support\Facades\Log;

Route::post('/records', function (Request $request) {

    Log::info('Creating new record', $request->all());
    $validated = $request->validate([
        'firma' => 'required|string|max:255',
        'mjrni_instrument' => 'required|string|max:255',
        'vrijeme' => 'required|date',
        'vrijednost' => 'required|numeric',
    ]);

    // Add current date for 'datum'
    $validated['datum'] = now()->toDateString();

    $record = Record::create($validated);

    return response()->json([
        'message' => 'Zapis uspješno kreiran.',
        'data' => $record,
    ], 201);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
