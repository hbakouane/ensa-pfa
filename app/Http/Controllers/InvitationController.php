<?php

namespace App\Http\Controllers;

use App\Models\CompanyInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class InvitationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:admin,recruiter,hiring_manager,interviewer'],
        ]);

        $existing = CompanyInvitation::withoutGlobalScopes()
            ->where('company_id', $request->user()->company_id)
            ->where('email', $validated['email'])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            return back()->withErrors(['email' => 'An invitation has already been sent to this email.']);
        }

        CompanyInvitation::create([
            'company_id' => $request->user()->company_id,
            'invited_by' => $request->user()->id,
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        return back()->with('success', 'Invitation sent successfully.');
    }

    public function accept(string $token)
    {
        $invitation = CompanyInvitation::withoutGlobalScopes()
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        if (Auth::check()) {
            return $this->processAcceptance($invitation, Auth::user());
        }

        return Inertia::render('Auth/AcceptInvitation', [
            'invitation' => $invitation->load('company'),
            'token' => $token,
        ]);
    }

    public function register(Request $request, string $token)
    {
        $invitation = CompanyInvitation::withoutGlobalScopes()
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($validated, $invitation) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'company_id' => $invitation->company_id,
                'type' => 'company',
            ]);

            setPermissionsTeamId($invitation->company_id);
            $user->assignRole($invitation->role);

            $invitation->update(['accepted_at' => now()]);

            return $user;
        });

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    private function processAcceptance(CompanyInvitation $invitation, User $user)
    {
        DB::transaction(function () use ($invitation, $user) {
            $user->update(['company_id' => $invitation->company_id]);
            setPermissionsTeamId($invitation->company_id);
            $user->assignRole($invitation->role);
            $invitation->update(['accepted_at' => now()]);
        });

        return redirect()->route('dashboard');
    }
}
