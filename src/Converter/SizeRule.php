<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class SizeRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class SizeRule extends AbstractSizeRule
{
    /**
     * @inheritdoc
     */
    protected function generateFileVueRules()
    {
        throw $this->createCannotGenerateRuleException('file');
    }

    /**
     * @return array
     * @throws \LogicException
     */
    protected function generateNumericVueRules()
    {
        $params = $this->requireLaravelParams(1);
        return ['between' => [$params[0], $params[0]]];
    }

    /**
     * @return array
     * @throws \LogicException
     */
    protected function generateStringVueRules()
    {
        $params = $this->requireLaravelParams(1);
        return [
            'min' => [$params[0]],
            'max' => [$params[0]],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function isValidForFileAttribute()
    {
        return false;
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
