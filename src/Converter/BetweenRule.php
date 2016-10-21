<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class BetweenRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class BetweenRule extends AbstractSizeRule
{
    /**
     * @inheritdoc
     */
    protected function generateFileVueRules()
    {
        $params = $this->requireLaravelParams(2);
        return ['size' => [$params[1]]];
    }

    /**
     * @return array
     */
    protected function generateNumericVueRules()
    {
        $params = $this->requireLaravelParams(2);
        return ['between' => $params];
    }

    /**
     * @return array
     * @throws \LogicException
     */
    protected function generateStringVueRules()
    {
        $params = $this->requireLaravelParams(2);
        return [
            'min' => [$params[0]],
            'max' => [$params[1]],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function isValidForFileAttribute()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function isValidForNumericAttribute()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function isValidForStringAttribute()
    {
        return true;
    }
}
