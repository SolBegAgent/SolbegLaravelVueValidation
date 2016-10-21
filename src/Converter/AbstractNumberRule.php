<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class AbstractNumberRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
abstract class AbstractNumberRule extends AbstractRule
{
    /**
     * @return string
     */
    abstract protected function getNumberRegexp();

    /**
     * @inheritdoc
     */
    public function getVueRules()
    {
        $regexp = $this->getNumberRegexp();
        return ['regex' => [$regexp]];
    }

    /**
     * @inheridoc
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
