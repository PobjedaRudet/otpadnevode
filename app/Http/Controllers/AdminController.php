<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::all();
        $availablePermissions = [
            'dashboard' => 'Dashboard',
            'companies' => 'Firme',
            'instruments' => 'Instrumenti',
            'reports' => 'Izvještaji',
            'summary' => 'Sumarni izvještaj',
            'instrumentSummary' => 'Instrument izvještaj',
        ];
        return view('admin.panel', compact('users', 'availablePermissions'));
    }

    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'role' => 'required|in:admin,user',
            'permissions' => 'array',
            'permissions.*' => 'string',
        ]);
        $user->role = $request->role;
        $user->permissions = $request->permissions ?? [];
        $user->save();
        return redirect()->route('admin.panel')->with('success', 'Uspješno promijenjena prava korisnika.');
    }
}
