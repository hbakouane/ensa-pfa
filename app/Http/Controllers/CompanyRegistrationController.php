<?php

namespace App\Http\Controllers;

use App\Actions\Company\RegisterCompanyAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class CompanyRegistrationController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/RegisterCompany');
    }

    public function store(Request $request, RegisterCompanyAction $action)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $result = $action->execute(
            ['company_name' => $validated['company_name'], 'industry' => $validated['industry'] ?? null, 'size' => $validated['size'] ?? null],
            ['name' => $validated['name'], 'email' => $validated['email'], 'password' => $validated['password']]
        );

        Auth::login($result['user']);

        return redirect()->route('dashboard');
    }
}
