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

        if (!$this->isValidRegexp($regexp)) {
            throw new \LogicException("Invalid regular expression in '{$this->getNormalizedLaravelRule()}' rule: '$regexp'. It cannot contain '|' and/or ',' chars.");
        }

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

    /**
     * @param string $regexp
     * @return boolean
     */
    protected function isValidRegexp($regexp)
    {
        return strpos($regexp, '|') === false && strpos($regexp, ',') === false;
    }
}
