<?php

namespace App\Services;

use App\Events\UserRegistered;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

/**
 * Création de compte, commune au site et à l'appli mobile.
 */
class AccountService
{
    /** Règles d'inscription (même exigence de mot de passe que le site). */
    public static function registrationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    public static function registrationMessages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé. Connectez-vous ou utilisez un autre email.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères. Veuillez ajouter des caractères pour atteindre 6 caractères minimum.',
        ];
    }

    /**
     * Crée le compte et prévient l'admin (événements identiques au site).
     */
    public function register(string $name, string $email, string $password, string $telephone, string $whatsapp): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'telephone' => $telephone,
            'whatsapp' => $whatsapp,
            'password' => Hash::make($password),
        ]);

        event(new Registered($user));
        event(new UserRegistered($user));

        return $user;
    }
}
