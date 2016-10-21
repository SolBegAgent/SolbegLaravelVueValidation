<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class RequiredRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class RequiredRule extends SimpleRule
{
    /**
     * @return array
     */
    public function getVueRules()
    {
        $parent = parent::getVueRules();
        return ['required' => array_shift($parent) ?: []];
    }
}
