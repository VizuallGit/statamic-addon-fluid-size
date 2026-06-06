<?php

namespace Vizuall\FluidSize\Fieldtypes;

use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Fields\Fieldtype;

class FluidFontPreview extends Fieldtype
{
    protected static $handle = 'fluid_font_preview';

    public function component(): string
    {
        return 'fluid-font-preview';
    }

    public function preload(): array
    {
        $handle = Site::default()->handle();
        $global = GlobalSet::find('theme_settings');
        $data   = $global?->in($handle)?->data();

        // Custom uploaded fonts
        $customRows  = $data?->get('custom_fonts', []) ?? [];
        $customFonts = collect($customRows)
            ->filter(fn ($r) => ! empty($r['file']) && ! str_starts_with((string) $r['file'], '{'))
            ->map(fn ($r) => [
                'file'     => (string) $r['file'],
                'variable' => (bool) ($r['variable'] ?? false),
                'weight'   => (string) ($r['weight'] ?? '400'),
            ])
            ->values()
            ->all();

        // All fonts in public/fonts (with weight extracted from filename)
        $dir        = public_path('fonts');
        $localFonts = [];
        if (is_dir($dir)) {
            foreach (glob($dir . '/*.{woff2,woff,ttf,otf}', GLOB_BRACE) ?: [] as $file) {
                $filename = basename($file);
                $stem     = pathinfo($filename, PATHINFO_FILENAME);
                $family   = FontFamilySelector::extractFamily($stem);
                if (! $family) continue;
                $variable     = (bool) preg_match('/VariableFont|Variable/i', $stem);
                $weight       = static::extractWeight($stem);
                $localFonts[] = ['file' => $filename, 'variable' => $variable, 'weight' => $weight];
            }
        }

        // Merge: custom fonts first, fill with local fonts not already listed
        $customFiles = array_column($customFonts, 'file');
        $extra       = array_values(array_filter($localFonts, fn ($f) => ! in_array($f['file'], $customFiles)));

        return ['customFonts' => array_merge($customFonts, $extra)];
    }

    private static function extractWeight(string $stem): string
    {
        $s = strtolower($stem);
        if (str_contains($s, 'thin'))                            return '100';
        if (preg_match('/extralight|ultralight/', $s))           return '200';
        if (str_contains($s, 'light'))                           return '300';
        if (str_contains($s, 'medium'))                          return '500';
        if (preg_match('/semibold|demibold/', $s))               return '600';
        if (preg_match('/extrabold|ultrabold|black|heavy/', $s)) return '800';
        if (str_contains($s, 'bold'))                            return '700';
        return '400';
    }

    public function preProcess($value): array
    {
        if (is_array($value)) return $value;
        return [];
    }

    public function process($value): array
    {
        if (!is_array($value)) return [];
        return $value;
    }
}
