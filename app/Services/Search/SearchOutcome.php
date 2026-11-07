<?php

namespace App\Services\Search;

/**
 * Résultat de la cascade. Les annonces du secours sont affichées,
 * mais ne comptent pas comme de vrais résultats dans les statistiques.
 */
class SearchOutcome
{
    /**
     * @param  list<int>  $ids  annonces dans l'ordre
     * @param  array<string, string>  $corrections  mot tapé => mot corrigé
     * @param  array{mots: list<string>, ville: ?string, prixMin: ?float, prixMax: ?float, prixBas: bool}|null  $comprehension
     */
    public function __construct(
        public readonly array $ids,
        public readonly ?int $niveau,
        public readonly int $realCount,
        public readonly array $corrections = [],
        public readonly ?array $comprehension = null,
        public readonly ?string $categorieProche = null,
    ) {
    }

    public function isFallback(): bool
    {
        return $this->niveau === SearchEngine::NIVEAU_SECOURS;
    }

    /**
     * Ce que le moteur a compris, critère par critère.
     * « q » est la recherche à relancer si l'on retire ce critère.
     *
     * @return list<array{label: string, q: string}>
     */
    public function criteres(): array
    {
        $understood = $this->comprehension ?? [];
        $parts = [];

        foreach ($understood['mots'] ?? [] as $mot) {
            $parts[] = [$mot, $mot];
        }

        if ($understood['ville'] ?? null) {
            $parts[] = [ucfirst($understood['ville']), $understood['ville']];
        }

        if ($understood['prixBas'] ?? false) {
            $parts[] = ['prix bas', 'pas cher'];
        }

        $min = $understood['prixMin'] ?? null;
        $max = $understood['prixMax'] ?? null;
        $amount = fn (float $value) => number_format($value, 0, ',', ' ') . ' F';

        if ($min !== null && $max !== null) {
            $parts[] = ["entre {$amount($min)} et {$amount($max)}", 'entre ' . (int) $min . ' et ' . (int) $max];
        } elseif ($max !== null) {
            $parts[] = ["moins de {$amount($max)}", 'moins de ' . (int) $max];
        } elseif ($min !== null) {
            $parts[] = ["plus de {$amount($min)}", 'plus de ' . (int) $min];
        }

        $criteres = [];
        foreach ($parts as $index => [$label]) {
            $others = $parts;
            unset($others[$index]);
            $criteres[] = ['label' => $label, 'q' => implode(' ', array_column($others, 1))];
        }

        return $criteres;
    }
}
