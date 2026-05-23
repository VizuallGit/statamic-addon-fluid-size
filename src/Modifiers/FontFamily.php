<?php

namespace Vizuall\FluidSize\Modifiers;

use Statamic\Modifiers\Modifier;
use Vizuall\FluidSize\Fieldtypes\FontFamilySelector;

class FontFamily extends Modifier
{
    protected static $handle = 'font_family';

    public function index($value, $params, $context): string
    {
        $stem = pathinfo((string) $value, PATHINFO_FILENAME);
        return FontFamilySelector::extractFamily($stem);
    }
}
