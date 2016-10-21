<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class IntegerRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class IntegerRule extends AbstractNumberRule
{
    /**
     * @inheritdoc
     */
    protected function getNumberRegexp()
    {
        return '^([\-\+]?(0|([1-9]\d*)))?$';
    }
}
