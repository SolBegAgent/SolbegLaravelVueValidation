<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class BooleanRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class BooleanRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function getVueRules()
    {
        return ['in' => ['1', '0']];
    }

    /**
     * @inheritdoc
     */
    public function isValid()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isDateRule()
    {
        return false;
    }
}
