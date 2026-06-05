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
                $fonts[]  = ['family' => $family, 'file' => $filename, 'variable' => $variable];
            }

            Statamic::provideToScript(['cp-fonts' => array_values($fonts)]);
        });
    }

    protected $scripts = [
        __DIR__.'/../resources/js/addon.js',
    ];

    protected $stylesheets = [
        __DIR__.'/../resources/css/addon.css',
    ];
}
