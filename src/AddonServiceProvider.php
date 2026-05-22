<?php

namespace Vizuall\FluidSize;

use Statamic\Providers\AddonServiceProvider as BaseAddonServiceProvider;

class AddonServiceProvider extends BaseAddonServiceProvider
{
    protected $fieldtypes = [
        Fieldtypes\FluidFontSize::class,
        Fieldtypes\FluidFontPreview::class,
        Fieldtypes\FluidSize::class,
    ];

    protected $scripts = [
        __DIR__.'/../resources/js/addon.js',
    ];

    protected $stylesheets = [
        __DIR__.'/../resources/css/addon.css',
    ];
}
