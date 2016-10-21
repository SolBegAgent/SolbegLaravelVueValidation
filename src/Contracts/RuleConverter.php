<?php

namespace Solbeg\VueValidation\Contracts;

/**
 * Interface RuleConverter
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
interface RuleConverter
{
    const OPTION_ATTRIBUTE_IS_NUMERIC = 0b00000010;
    const OPTION_ATTRIBUTE_IS_FILE = 0b00000001;

    /**
     * @return array in rule => params format.
     */
    public function getVueRules();

    /**
     * @return boolean
     */
    public function isValid();

    /**
     * @return boolean
     */
    public function isDateRule();

    /**
     * @return integer
     */
    public function getAttributeOptions();

    /**
     * @return string
     */
    public function __toString();
}
