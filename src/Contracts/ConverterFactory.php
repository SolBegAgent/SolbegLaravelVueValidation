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
     * @param string $attribute
     * @param string $message
     * @param string $rule
     * @param array $params
     * @param integer $attributeOptions
     * @return RuleConverter
     */
    public function make($inputName, $attribute, $message, $rule, array $params, $attributeOptions);

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
