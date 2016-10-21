<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class TimezoneRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class TimezoneRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function getVueRules()
    {
        return ['in' => $this->getPossibleTimezones() ?: []];
    }

    /**
     * @inheridoc
     */
    public function isValid()
    {
        return (bool) $this->getPossibleTimezones();
    }

    /**
     * @inheritdoc
     */
    public function isDateRule()
    {
        return false;
    }

    /**
     * @return string[]|false
     */
    protected function getPossibleTimezones()
    {
        return \DateTimeZone::listIdentifiers();
    }
}
