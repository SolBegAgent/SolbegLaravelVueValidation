<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class SimpleRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class SimpleRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function getVueRules()
    {
        $rule = $this->getNormalizedLaravelRule();
        $params = $this->getLaravelParams();
        return [$rule => $params];
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
