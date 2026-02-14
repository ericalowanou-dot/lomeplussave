<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Vérifier s'il y a un paramètre redirect dans l'URL
        $redirectUrl = $request->input('redirect');
        if ($redirectUrl) {
            $decodedUrl = urldecode($redirectUrl);
            // Vérifier que l'URL est valide et du même domaine pour la sécurité
            if (filter_var($decodedUrl, FILTER_VALIDATE_URL)) {
                $parsedUrl = parse_url($decodedUrl);
                $appHost = $request->getHost();
                if ($parsedUrl && isset($parsedUrl['host']) && $parsedUrl['host'] === $appHost) {
                    return redirect($decodedUrl);
                }
            }
        }

        // Rediriger les administrateurs vers le dashboard d'administration
        if (auth()->user()->isAdmin()) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        return redirect()->intended(route('articles.index', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
