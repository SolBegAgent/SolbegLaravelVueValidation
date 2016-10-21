<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class DigitsBetweenRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class DigitsBetweenRule extends AbstractNumberRule
{
    /**
     * @inheritdoc
     */
    protected function getNumberRegexp()
    {
        return '^(\d+)?$';
    }

    /**
     * @inheritdoc
     */
    public function getVueRules()
    {
        $params = $this->requireLaravelParams(2);
        return array_merge(parent::getVueRules(), [
            'min' => [$params[0]],
            'max' => [$params[1]],
        ]);
    }
}
