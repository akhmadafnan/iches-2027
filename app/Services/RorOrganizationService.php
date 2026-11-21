<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class RorOrganizationService
{
    private const BASE_URL = 'https://api.ror.org/v2/organizations';

    /**
     * @return list<array{id: string, name: string, aliases: string, location: string}>
     */
    public function search(string $term): array
    {
        $term = trim($term);

        if (mb_strlen($term) < 2) {
            return [];
        }

        $term = mb_substr($term, 0, 120);
        $key = 'ror:v2:search:'.hash('sha256', mb_strtolower($term));

        /** @var list<array{id: string, name: string, aliases: string, location: string}> $results */
        $results = Cache::remember($key, now()->addHours(12), function () use ($term): array {
            $response = Http::acceptJson()->timeout(7)->get(self::BASE_URL, ['query' => $term])->throw();
            $items = $response->json('items', []);

            if (! is_array($items)) {
                return [];
            }

            $results = [];

            foreach ($items as $item) {
                if (! is_array($item) || ($item['status'] ?? '') !== 'active') {
                    continue;
                }

                $organization = self::normalize($item);

                if ($organization['id'] === '' || $organization['name'] === '') {
                    continue;
                }

                $results[] = $organization;

                if (count($results) >= 10) {
                    break;
                }
            }

            return $results;
        });

        return $results;
    }

    /**
     * @return array{id: string, name: string, aliases: string, location: string}|null
     */
    public function resolve(string $rorId): ?array
    {
        $id = self::extractId(trim($rorId));

        if ($id === null) {
            throw new InvalidArgumentException('Invalid ROR identifier format.');
        }

        /** @var array{id: string, name: string, aliases: string, location: string}|null $result */
        $result = Cache::remember('ror:v2:organization:'.$id, now()->addDays(7), function () use ($id): ?array {
            $response = Http::acceptJson()->timeout(7)->get(self::BASE_URL.'/'.$id);

            if ($response->notFound()) {
                return null;
            }

            $response->throw();
            $data = $response->json();

            if (! is_array($data) || ($data['status'] ?? '') !== 'active') {
                return null;
            }

            $organization = self::normalize($data);

            return $organization['name'] !== '' ? $organization : null;
        });

        return $result;
    }

    private static function extractId(string $value): ?string
    {
        if (preg_match('~^(?:https://ror\.org/)?(0[a-z0-9]{8})$~i', $value, $matches) !== 1) {
            return null;
        }

        return strtolower($matches[1]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{id: string, name: string, aliases: string, location: string}
     */
    private static function normalize(array $data): array
    {
        $id = is_string($data['id'] ?? null) ? $data['id'] : '';
        $shortId = self::extractId($id);

        if ($shortId === null) {
            return ['id' => '', 'name' => '', 'aliases' => '', 'location' => ''];
        }

        $rawNames = $data['names'] ?? [];
        $name = '';
        $fallback = '';
        $label = '';
        $acronyms = [];

        if (is_array($rawNames)) {
            foreach ($rawNames as $entry) {
                if (! is_array($entry) || ! is_string($entry['value'] ?? null)) {
                    continue;
                }

                $value = trim($entry['value']);
                $types = is_array($entry['types'] ?? null) ? $entry['types'] : [];

                if ($fallback === '') {
                    $fallback = $value;
                }

                if (in_array('ror_display', $types, true)) {
                    $name = $value;
                } elseif (in_array('label', $types, true) && $label === '') {
                    $label = $value;
                }

                if (in_array('acronym', $types, true) || in_array('alias', $types, true)) {
                    $acronyms[] = $value;
                }
            }
        }

        $name = $name !== '' ? $name : ($label !== '' ? $label : $fallback);
        $rawLocations = $data['locations'] ?? [];
        $place = is_array($rawLocations) ? ($rawLocations[0] ?? null) : null;
        $geo = is_array($place) && is_array($place['geonames_details'] ?? null) ? $place['geonames_details'] : [];
        $locationParts = [];

        foreach (['name', 'country_name'] as $key) {
            if (is_string($geo[$key] ?? null) && trim($geo[$key]) !== '') {
                $locationParts[] = trim($geo[$key]);
            }
        }

        return [
            'id' => 'https://ror.org/'.$shortId,
            'name' => Str::limit($name, 255, ''),
            'aliases' => Str::limit(implode(', ', array_slice(array_unique($acronyms), 0, 3)), 180, ''),
            'location' => Str::limit(implode(', ', $locationParts), 180, ''),
        ];
    }
}
