<?php

namespace Vizuall\FluidSize;

use Statamic\Modifiers\Modifier;
use Statamic\Providers\AddonServiceProvider as BaseAddonServiceProvider;
use Statamic\Statamic;

class AddonServiceProvider extends BaseAddonServiceProvider
{
    protected $fieldtypes = [
        Fieldtypes\FluidFontSize::class,
        Fieldtypes\FluidFontPreview::class,
        Fieldtypes\FluidSize::class,
        Fieldtypes\FontFamilySelector::class,
        Fieldtypes\FontUploader::class,
    ];

    public function bootAddon(): void
    {
        Modifier::register('font_family', Modifiers\FontFamily::class);

        Statamic::booted(function () {
            $dir = public_path('fonts');
            if (! is_dir($dir)) return;

            $files = glob($dir . '/*.{woff2,woff,ttf,otf}', GLOB_BRACE) ?: [];
            $fonts = [];

            foreach ($files as $file) {
                $filename = basename($file);
                $stem     = pathinfo($filename, PATHINFO_FILENAME);
                $family   = Fieldtypes\FontFamilySelector::extractFamily($stem);
                if (! $family) continue;
                $variable = (bool) preg_match('/VariableFont|Variable/i', $stem);
                $weight   = static::extractWeight($stem);
                $fonts[]  = ['family' => $family, 'file' => $filename, 'variable' => $variable, 'weight' => $weight];
            }

            Statamic::provideToScript(['cp-fonts' => array_values($fonts)]);
        });
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

    protected $scripts = [
        __DIR__.'/../resources/js/addon.js',
    ];

    protected $stylesheets = [
        __DIR__.'/../resources/css/addon.css',
    ];
}
