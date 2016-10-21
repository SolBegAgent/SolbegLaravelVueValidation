<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class NumericRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class NumericRule extends AbstractNumberRule
{
    /**
     * @inheritdoc
     */
    protected function getNumberRegexp()
    {
        return '^([-+]?\d*\.?\d+([eE][-+]?\d+)?)?$';
    }
}
