<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        \Illuminate\Support\Facades\Log::info('Login Attempt', ['email' => $request->email]);

        // DEBUG: Manual Lookup
        $user = \App\Models\User::where('email', $request->email)->first();
        if ($user) {
            \Illuminate\Support\Facades\Log::info('User Found Manually', ['id' => $user->id, 'password_hash' => $user->password]);
            
            // OPTIONAL: Force login to test Session persistence
            // Auth::login($user);
            // $request->session()->regenerate();
            // return redirect()->intended(RouteServiceProvider::HOME);
        } else {
             \Illuminate\Support\Facades\Log::error('User NOT Found in DB', ['db' => \Illuminate\Support\Facades\DB::connection()->getDatabaseName()]);
        }
        
        $request->authenticate();
        \Illuminate\Support\Facades\Log::info('User Authenticated', ['id' => Auth::id()]);

        $request->session()->regenerate();
        \Illuminate\Support\Facades\Log::info('Session Regenerated', ['id' => session()->getId()]);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
