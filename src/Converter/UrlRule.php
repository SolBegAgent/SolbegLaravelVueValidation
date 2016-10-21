<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class UrlRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class UrlRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function getVueRules()
    {
        return ['url' => []];
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
