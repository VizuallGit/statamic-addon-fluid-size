<?php

namespace Vizuall\FluidSize\Fieldtypes;

use Statamic\Fields\Fieldtype;

class FluidFontPreview extends Fieldtype
{
    protected static $handle = 'fluid_font_preview';

    public function component(): string
    {
        return 'fluid-font-preview';
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
