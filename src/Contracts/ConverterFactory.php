<?php

namespace Solbeg\VueValidation\Contracts;

/**
 * Interface ConverterFactory
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
interface ConverterFactory
{
    /**
     * @param string $inputName
     * @param string $rule
     * @param array $params
     * @param array $allRules
     * @param integer $attributeOptions
     * @return RuleConverter
     */
    public function make($inputName, $rule, array $params, array $allRules, $attributeOptions);

    /**
     * @param string $rule
     * @return boolean
     */
    public function knows($rule);

    /**
     * @param string $rule
     * @param string|\Closure $config
     * @return void
     */
    public function extend($rule, $config);
}
