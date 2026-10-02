<?php

namespace App\Services;

use App\Mail\AdminActivityMail;
use App\Models\Article;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Prévient l'administrateur par mail des actions des vendeurs.
 * L'envoi a lieu après la réponse HTTP : le vendeur n'attend pas le SMTP,
 * et une panne de mail n'empêche jamais l'enregistrement.
 */
class AdminMailNotifier
{
    private const FIELD_LABELS = [
        'titre' => 'Titre',
        'prix_ht' => 'Prix',
        'lieu' => 'Lieu',
        'description' => 'Description',
        'sous_categorie_id' => 'Catégorie',
        'neuf' => 'État',
        'livraison' => 'Livraison',
        'photo' => 'Photos',
    ];

    public static function articleCreated(Article $article, User $seller): void
    {
        if ($seller->isAdmin()) {
            return;
        }

        static::send(new AdminActivityMail(
            subjectLine: 'Nouvel article à valider : ' . Str::limit($article->titre, 60),
            heading: 'Un vendeur vient de publier un article',
            details: static::sellerDetails($seller) + static::articleDetails($article),
            bodyText: Str::limit($article->description, 500),
            actionUrl: route('admin.articles.show', $article),
            actionLabel: 'Voir l\'article dans l\'admin',
            replyToAddress: $seller->email,
            replyToName: $seller->name,
        ));
    }

    /**
     * @param  array<int, string>  $changedFields  Colonnes modifiées (getDirty avant save)
     */
    public static function articleUpdated(Article $article, User $seller, array $changedFields, bool $resubmitted): void
    {
        if ($seller->isAdmin()) {
            return;
        }

        $labels = collect($changedFields)
            ->map(fn ($field) => str_starts_with($field, 'photo') ? 'Photos' : (self::FIELD_LABELS[$field] ?? null))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $details = static::sellerDetails($seller) + static::articleDetails($article);
        $details['Champs modifiés'] = $labels ? implode(', ', $labels) : 'Aucun changement détecté';
        $details['Validation'] = $resubmitted
            ? 'Renvoyé en validation (était publié ou bloqué)'
            : 'Statut inchangé (' . $article->status . ')';

        static::send(new AdminActivityMail(
            subjectLine: ($resubmitted ? 'Article modifié à revalider : ' : 'Article modifié : ') . Str::limit($article->titre, 60),
            heading: 'Un vendeur a modifié un article',
            details: $details,
            bodyText: null,
            actionUrl: route('admin.articles.show', $article),
            actionLabel: 'Voir l\'article dans l\'admin',
            replyToAddress: $seller->email,
            replyToName: $seller->name,
        ));
    }

    public static function messageReceived(Message $message, User $sender): void
    {
        if ($sender->isAdmin()) {
            return;
        }

        $details = static::sellerDetails($sender);
        $details['Type'] = $message->parent_message_id ? 'Réponse à un message' : 'Nouveau message';
        if ($message->subject) {
            $details['Sujet'] = $message->subject;
        }

        static::send(new AdminActivityMail(
            subjectLine: 'Nouveau message de ' . $sender->name,
            heading: 'Un utilisateur vous a envoyé un message',
            details: $details,
            bodyText: $message->body,
            actionUrl: route('admin.messages.show', $message),
            actionLabel: 'Répondre dans l\'admin',
            replyToAddress: $sender->email,
            replyToName: $sender->name,
        ));
    }

    private static function sellerDetails(User $seller): array
    {
        return array_filter([
            'Vendeur' => $seller->name,
            'Email' => $seller->email,
            'Téléphone' => $seller->telephone ?? $seller->whatsapp ?? null,
        ]);
    }

    private static function articleDetails(Article $article): array
    {
        return array_filter([
            'Article' => $article->titre,
            'Prix' => $article->prix_ht !== null ? number_format((float) $article->prix_ht, 0, ',', ' ') . ' FCFA' : null,
            'Lieu' => $article->lieu,
        ]);
    }

    private static function send(AdminActivityMail $mail): void
    {
        $to = config('mail.admin_notification_address');
        if (! $to) {
            return;
        }

        app()->terminating(function () use ($mail, $to) {
            try {
                Mail::to($to)->send($mail);
            } catch (\Throwable $e) {
                Log::warning('Mail admin non envoyé : ' . $e->getMessage(), ['subject' => $mail->subjectLine]);
            }
        });
    }
}
