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
        foreach ($this->getLaravelParams() as $param) {
            if (!is_scalar($param) && !is_null($param)) {
                return false;
            } elseif (strpos($param, '|') !== false || strpos($param, ',') !== false) {
                return false;
            }
        }
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
