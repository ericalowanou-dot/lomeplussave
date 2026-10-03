<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MeResource;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * Connexion de l'appli mobile par jeton (Laravel Sanctum).
 * L'appli envoie ensuite « Authorization: Bearer <jeton> » à chaque requête.
 */
class AuthController extends Controller
{
    public function register(Request $request, AccountService $accounts): JsonResponse
    {
        $data = $request->validate(AccountService::registrationRules() + [
            'telephone' => ['required', 'string', 'max:20'],
            'whatsapp' => ['required', 'string', 'max:20'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ], AccountService::registrationMessages() + [
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'whatsapp.required' => 'Le numéro WhatsApp est obligatoire.',
        ]);

        $user = $accounts->register($data['name'], $data['email'], $data['password'], $data['telephone'], $data['whatsapp']);

        return $this->tokenResponse($user, $data['device_name'] ?? null, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email ou mot de passe incorrect.',
            ]);
        }

        if ($user->isBlocked()) {
            return response()->json([
                'message' => 'Votre compte a été bloqué. Raison : ' . ($user->block_reason ?? 'Non spécifiée'),
                'code' => 'account_blocked',
            ], 403);
        }

        return $this->tokenResponse($user, $data['device_name'] ?? null);
    }

    /** Déconnecte cet appareil (le jeton utilisé est supprimé). */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }

    /** Déconnecte tous les appareils de l'utilisateur. */
    public function logoutEverywhere(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Déconnecté de tous les appareils.']);
    }

    /** Envoie le même email de réinitialisation que le site. */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();
        if ($user && ! $user->isBlocked()) {
            Password::sendResetLink($request->only('email'));
        }

        // Même réponse que le compte existe ou non : on ne révèle pas les emails inscrits
        return response()->json([
            'message' => 'Si un compte existe avec cet email, un lien de réinitialisation vient d\'être envoyé.',
        ]);
    }

    private function tokenResponse(User $user, ?string $deviceName, int $status = 200): JsonResponse
    {
        $token = $user->createToken($deviceName ?: 'appli-mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new MeResource($user->fresh()),
        ], $status);
    }
}
