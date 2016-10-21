<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Trait ToStringTrait
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
trait ToStringTrait
{
    /**
     * @return array
     */
    abstract public function getVueRules();

    /**
     * @return string
     */
    public function toVueString()
    {
        $rules = [];
        foreach ($this->getVueRules() as $rule => $params) {
            $rules[] = $rule . ($params ? ':' . implode(',', $params) : '');
        }
        return implode('|', $rules);
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        try {
            return (string) $this->toVueString();
        } catch (\Exception $ex) {
            trigger_error((string) $ex, E_USER_ERROR);
            return '';
        }
    }
}
