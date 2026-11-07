<?php

namespace App\Services\Search;

use Illuminate\Support\Str;

/**
 * Transforme un texte en mots comparables : minuscules, sans accents, sans ponctuation.
 */
class TextNormalizer
{
    public const MAX_WORD_LENGTH = 64;

    /** Mots français sans valeur de recherche, plus les verbes d'intention (« je cherche »). */
    private const STOP_WORDS = [
        'a', 'ai', 'ainsi', 'alors', 'au', 'aussi', 'autre', 'aux', 'avec', 'avoir', 'c', 'ca', 'car', 'ce', 'ceci',
        'cela', 'ces', 'cet', 'cette', 'comme', 'd', 'dans', 'de', 'des', 'donc', 'du', 'elle', 'elles', 'en', 'encore',
        'est', 'et', 'etc', 'eu', 'il', 'ils', 'j', 'je', 'l', 'la', 'le', 'les', 'leur', 'leurs', 'lui', 'm', 'ma',
        'mais', 'me', 'mes', 'moi', 'mon', 'n', 'ne', 'ni', 'nos', 'notre', 'nous', 'on', 'ont', 'ou', 'par', 'pas',
        'plus', 'pour', 'qu', 'que', 'quel', 'quelle', 'qui', 's', 'sa', 'sans', 'se', 'ses', 'si', 'son', 'sont',
        'sous', 'sur', 't', 'ta', 'te', 'tes', 'toi', 'ton', 'tous', 'tout', 'tres', 'tu', 'un', 'une', 'vos', 'votre',
        'vous', 'y',
        'achat', 'achete', 'acheter', 'besoin', 'bonjour', 'cherche', 'chercher', 'cherches', 'merci', 'recherche',
        'recherches', 'souhaite', 'svp', 'trouver', 'vend', 'vendre', 'vends', 'vente', 'veux', 'vouloir', 'voudrais',
    ];

    public function normalize(string $text): string
    {
        $text = Str::lower(Str::ascii($text));

        return trim((string) preg_replace('/[^a-z0-9]+/', ' ', $text));
    }

    /** @return list<string> */
    public function words(string $text): array
    {
        $normalized = $this->normalize($text);

        return $normalized === '' ? [] : explode(' ', $normalized);
    }

    public function isStopWord(string $word): bool
    {
        static $stopWords = null;
        $stopWords ??= array_flip(self::STOP_WORDS);

        return isset($stopWords[$word]);
    }

    /** Racine légère : pluriels et e final (« voitures » et « voiture » donnent la même). */
    public function stem(string $word): string
    {
        if (ctype_digit($word) || strlen($word) <= 3) {
            return $word;
        }

        $word = match (true) {
            str_ends_with($word, 'eaux') => substr($word, 0, -1),
            str_ends_with($word, 'aux') => substr($word, 0, -2) . 'l',
            str_ends_with($word, 's'), str_ends_with($word, 'x') => substr($word, 0, -1),
            default => $word,
        };

        return strlen($word) > 4 && str_ends_with($word, 'e') ? substr($word, 0, -1) : $word;
    }

    /** Clé de prononciation française : « telefone » et « telephone » donnent la même. */
    public function phonetic(string $word): string
    {
        if (preg_match('/[0-9]/', $word)) {
            return $word;
        }

        $key = strtr($word, ['ph' => 'f', 'qu' => 'k', 'ck' => 'k', 'sch' => 'ch', 'eau' => 'o', 'au' => 'o', 'ai' => 'e', 'ei' => 'e', 'y' => 'i', 'w' => 'v', 'z' => 's']);
        $key = (string) preg_replace('/c(?=[ei])/', 's', $key);
        $key = (string) preg_replace('/c(?!h)/', 'k', $key);
        $key = (string) preg_replace('/g(?=[ei])/', 'j', $key);
        $key = str_replace('gu', 'g', $key);
        $key = (string) preg_replace('/(?<!c)h/', '', $key);
        $key = (string) preg_replace('/[ae][nm](?=[^aeiou]|$)/', 'an', $key);
        $key = (string) preg_replace('/(.)\1+/', '$1', $key);
        $key = (string) preg_replace('/(?<=.)(er|ez|et|es|e|s|t|d|x)$/', '', $key);

        return $key === '' ? $word : $key;
    }

    /** Fautes tolérées selon la longueur du mot. */
    public function allowedTypos(string $word): int
    {
        return match (true) {
            strlen($word) <= 3 => 0,
            strlen($word) <= 7 => 1,
            strlen($word) <= 11 => 2,
            default => 3,
        };
    }

    /** Distance d'édition où l'inversion de deux lettres compte pour une seule faute. */
    public function distance(string $a, string $b): int
    {
        $lengthA = strlen($a);
        $lengthB = strlen($b);
        $previous = null;
        $current = range(0, $lengthB);

        for ($i = 1; $i <= $lengthA; $i++) {
            $beforePrevious = $previous;
            $previous = $current;
            $current = [$i];

            for ($j = 1; $j <= $lengthB; $j++) {
                $cost = $a[$i - 1] === $b[$j - 1] ? 0 : 1;
                $current[$j] = min($previous[$j] + 1, $current[$j - 1] + 1, $previous[$j - 1] + $cost);

                if ($i > 1 && $j > 1 && $a[$i - 1] === $b[$j - 2] && $a[$i - 2] === $b[$j - 1]) {
                    $current[$j] = min($current[$j], $beforePrevious[$j - 2] + 1);
                }
            }
        }

        return $current[$lengthB];
    }

    /**
     * « iphone13 » → [iphone, 13]. Renvoie le mot seul s'il ne mélange pas lettres et chiffres.
     *
     * @return list<string>
     */
    public function splitLettersAndDigits(string $word): array
    {
        return preg_split('/(?<=[a-z])(?=[0-9])|(?<=[0-9])(?=[a-z])/', $word) ?: [$word];
    }
}
