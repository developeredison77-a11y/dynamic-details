<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            $employee = Employee::query()->where('email', $credentials['email'])->first();
            $userAlreadyExists = User::query()->where('email', $credentials['email'])->exists();

            if (
                ! $userAlreadyExists
                && $employee
                && filled($employee->password)
                && Hash::check($credentials['password'], $employee->password)
            ) {
                $user = User::query()->create([
                    'name' => $employee->name_en,
                    'email' => $employee->email,
                    'password' => $employee->password,
                    'role_id' => $employee->role_id,
                ]);

                Auth::login($user, $remember);
                $request->session()->regenerate();

                return redirect()->intended(route('dashboard'));
            }

            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
