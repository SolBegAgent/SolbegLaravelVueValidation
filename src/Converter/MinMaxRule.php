<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class MinMaxRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class MinMaxRule extends AbstractSizeRule
{
    /**
     * @inheritdoc
     */
    protected function generateFileVueRules()
    {
        if ($this->isMaxRule()) {
            return ['size' => $this->getLaravelParams()];
        }
        throw $this->createCannotGenerateRuleException('file');
    }

    /**
     * @inheritdoc
     */
    protected function generateNumericVueRules()
    {
        $params = $this->requireLaravelParams(1);
        $limitValue = $params[0];

        if ($this->isMinRule()) {
            return ['between' => [$limitValue, 'Infinity']];
        } elseif ($this->isMaxRule()) {
            return ['between' => ['-Infinity', $limitValue]];
        }

        throw $this->createCannotGenerateRuleException();
    }

    /**
     * @inheritdoc
     */
    protected function generateStringVueRules()
    {
        $rule = $this->getNormalizedLaravelRule();
        $params = $this->getLaravelParams();
        return [$rule => $params];
    }

    /**
     * @inheritdoc
     */
    protected function isValidForFileAttribute()
    {
        return $this->isMaxRule();
    }

    /**
     * @inheritdoc
     */
    protected function isValidForNumericAttribute()
    {
        return $this->isMaxRule() || $this->isMinRule();
    }

    /**
     * @inheritdoc
     */
    protected function isValidForStringAttribute()
    {
        return true;
    }

    /**
     * @return bool
     */
    protected function isMinRule()
    {
        return strpos($this->getNormalizedLaravelRule(), 'min') !== false;
    }

    /**
     * @return bool
     */
    protected function isMaxRule()
    {
        return strpos($this->getNormalizedLaravelRule(), 'max') !== false;
    }
}
