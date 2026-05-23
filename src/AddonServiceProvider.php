<?php

namespace Vizuall\FluidSize;

use Statamic\Modifiers\Modifier;
use Statamic\Providers\AddonServiceProvider as BaseAddonServiceProvider;

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
    }

    protected $scripts = [
        __DIR__.'/../resources/js/addon.js',
    ];

    protected $stylesheets = [
        __DIR__.'/../resources/css/addon.css',
    ];
}
