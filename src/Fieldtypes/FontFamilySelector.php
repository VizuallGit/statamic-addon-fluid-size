<?php

namespace Vizuall\FluidSize\Fieldtypes;

use Illuminate\Support\Facades\Cache;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Fields\Fieldtype;

class FontFamilySelector extends Fieldtype
{
    protected static $handle = 'font_family_selector';

    public function component(): string
    {
        return 'font-family-selector';
    }

    public function preload(): array
    {
        $local  = static::scanFamilies();
        $adobe  = static::adobeFamilies();
        $merged = array_values(array_unique(array_merge($local, $adobe)));
        sort($merged);

        return ['fonts' => $merged];
    }

    public static function scanFamilies(): array
    {
        $dir = public_path('fonts');
        if (!is_dir($dir)) return [];

        $files = glob($dir . '/*.{woff2,woff,ttf,otf}', GLOB_BRACE) ?: [];
        $families = [];

        foreach ($files as $file) {
            $stem   = pathinfo($file, PATHINFO_FILENAME);
            $family = static::extractFamily($stem);
            if ($family) {
                $families[$family] = true;
            }
        }

        ksort($families);
        return array_keys($families);
    }

    public static function adobeFamilies(): array
    {
        try {
            $global = GlobalSet::findByHandle('theme_settings');
            if (!$global) return [];

            $vars = $global->in(Site::default()->handle());
            if (!$vars) return [];

            $kits = $vars->get('adobe_kits') ?? [];
            $families = [];

            foreach ($kits as $kit) {
                $url = $kit['url'] ?? null;
                if (!$url) continue;

                $css = Cache::remember('adobe_kit_' . md5($url), now()->addHours(24), function () use ($url) {
                    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
                    return @file_get_contents($url, false, $ctx) ?: '';
                });

                preg_match_all('/font-family:\s*["\']([^"\']+)["\']/', $css, $matches);
                foreach ($matches[1] as $family) {
                    $families[] = $family;
                }
            }

            return array_values(array_unique($families));
        } catch (\Throwable) {
            return [];
        }
    }

    public static function extractFamily(string $stem): string
    {
        if (preg_match('/icon|symbol|awesome|material/i', $stem)) {
            return '';
        }

        $suffixes = [
            'VariableFont', 'Variable',
            'ExtraLight', 'UltraLight', 'ExtraBold', 'UltraBold', 'SemiBold', 'DemiBold',
            'Thin', 'Light', 'Regular', 'Normal', 'Medium', 'Bold', 'Black', 'Heavy',
            'Italic', 'Oblique', 'Condensed', 'Expanded', 'Narrow',
            'wght', 'ital',
        ];

        $pattern = '/[-_ ](' . implode('|', $suffixes) . ').*$/i';
        $family  = preg_replace($pattern, '', $stem);
        $family  = preg_replace('/[-_]?[1-9]00$/', '', $family);

        return trim($family);
    }
}
