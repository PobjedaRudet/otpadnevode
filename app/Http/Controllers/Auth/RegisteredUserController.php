<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function create()
    {
        // Ova forma je sada dostupna samo administratoru (ruta je već zaštićena), ali dodatna provjera
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Nemate administratorska prava.');
        }
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','lowercase','email','max:255','unique:users,email'],
            'password' => ['required','confirmed', Rules\Password::defaults()],
        ]);

        // Dodatna zaštita – samo admin može kreirati korisnika
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Nemate administratorska prava.');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        // Ne logujemo novog korisnika – admin ostaje prijavljen
        return redirect()->route('admin.panel')->with('status', 'Korisnik uspješno kreiran.');
    }
}
