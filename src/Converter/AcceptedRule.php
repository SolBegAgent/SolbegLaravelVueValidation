<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class AcceptedRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class AcceptedRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function getVueRules()
    {
        return ['in' => [
            'yes',
            'on',
            '1',
            'true',
        ]];
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
