<?php

namespace App\Services\Search;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Suggestions pendant la saisie. Elles viennent uniquement des annonces et des catégories,
 * jamais de ce que d'autres visiteurs ont tapé.
 */
class SearchSuggestions
{
    public function __construct(private TextNormalizer $text, private SearchEngine $engine)
    {
    }

    /** @return array{suggestions: list<array{type: string, label: string}>, correction: ?string} */
    public function for(string $q): array
    {
        $normalized = $this->text->normalize($q);

        if (strlen($normalized) < 2) {
            return ['suggestions' => [], 'correction' => null];
        }

        return Cache::remember(
            'search:suggestions:' . sha1($normalized . Cache::get(SearchIndexer::VERSION_KEY)),
            config('search.cache_seconds'),
            function () use ($q, $normalized) {
                $suggestions = [...$this->categories($normalized), ...$this->titles($normalized)];

                return [
                    'suggestions' => array_slice($suggestions, 0, config('search.suggestions_max')),
                    'correction' => $suggestions === [] ? $this->correction($q) : null,
                ];
            }
        );
    }

    private function categories(string $normalized): array
    {
        return DB::table('sous_categories')->pluck('nom')
            ->merge(DB::table('categories')->pluck('nom'))
            ->filter(fn ($nom) => str_contains(" {$this->text->normalize($nom)}", " {$normalized}"))
            ->unique()
            ->take(3)
            ->map(fn ($nom) => ['type' => 'categorie', 'label' => $nom])
            ->values()
            ->all();
    }

    /** Titres dont un mot commence par la saisie : ceux des annonces boostées, puis les plus récents. */
    private function titles(string $normalized): array
    {
        return DB::table('search_documents')
            ->join('articles', 'articles.id', '=', 'search_documents.article_id')
            ->where('search_documents.titre', 'like', "% {$normalized}%")
            ->orderByRaw('(search_documents.boosted_until IS NOT NULL AND search_documents.boosted_until > ?) DESC', [now()])
            ->orderByDesc('search_documents.publie_le')
            ->limit(config('search.suggestions_max') * 3)
            ->pluck('articles.titre')
            ->map(fn ($titre) => trim($titre))
            ->unique()
            ->take(config('search.suggestions_max'))
            ->map(fn ($titre) => ['type' => 'titre', 'label' => $titre])
            ->values()
            ->all();
    }

    private function correction(string $q): ?string
    {
        $corrections = $this->engine->suggestCorrections($q);

        if ($corrections === []) {
            return null;
        }

        return implode(' ', array_map(fn ($word) => $corrections[$word] ?? $word, $this->text->words($q)));
    }
}
