<?php

namespace Solbeg\VueValidation;

use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

use Solbeg\VueValidation\Contracts\ConverterFactory;
use Solbeg\VueValidation\Contracts\RuleConverter;
use Solbeg\VueValidation\Wrappers\FormRequestWrapper;
use Solbeg\VueValidation\Wrappers\ValidatorWrapper;

/**
 * Class RulesParser
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class RulesParser
{
    /**
     * @var FormRequest
     */
    private $request;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Validator|null
     */
    private $validator;

    /**
     * @var ConverterFactory
     */
    private $converter;

    /**
     * RulesParser constructor.
     *
     * @param Container $container
     * @param FormRequest $request
     * @param ConverterFactory $converter
     */
    public function __construct(Container $container, FormRequest $request, ConverterFactory $converter)
    {
        $this->container = $container;
        $this->request = $request;
        $this->converter = $converter;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return FormRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return ConverterFactory
     */
    public function getConverter()
    {
        return $this->converter;
    }

    /**
     * @return Validator
     */
    public function getValidator()
    {
        if ($this->validator !== null) {
            return $this->validator;
        }

        $validator = FormRequestWrapper::fetchValidatorInstance($this->getRequest());
        if (!$validator instanceof Validator) {
            throw new \LogicException('The "' . static::class . '" works only with "' . Validator::class . '" instances.');
        }
        return $this->validator = $validator;
    }

    /**
     * @param string $inputName
     * @return \Solbeg\VueValidation\Contracts\RuleConverter[]
     */
    public function generateVueRules($inputName)
    {
        $laravelRules = $this->parseLaravelRules($inputName);
        $attributeOptions = $this->detectAttributeOptions($laravelRules);
        $factory = $this->getConverter();
        $validator = $this->getValidator();
        $result = [];

        foreach ($laravelRules as $data) {
            list($rule, $params, $attribute) = $data;
            if (!$factory->knows($rule)) {
                continue;
            }

            $message = ValidatorWrapper::generateErrorMessage($validator, $attribute, $rule, $params);
            $converter = $factory->make($inputName, $attribute, $message, $rule, $params, $attributeOptions);
            if ($converter->isValid()) {
                $result[] = $converter;
            }
        }

        return $this->removeInvalidDateRules($result);
    }

    /**
     * @param string $inputName
     * @return array[]
     */
    protected function parseLaravelRules($inputName)
    {
        $validator = $this->getValidator();
        $rules = ValidatorWrapper::fetchInitialRules($validator);

        $result = [];
        foreach ($rules as $attribute => $rules) {
            if ($this->isAttributeMatchesInputName($attribute, $inputName)) {
                foreach (ValidatorWrapper::extractRulesWithParams($validator, $rules) as $ruleData) {
                    $result[] = array_merge($ruleData, [$attribute]);
                }
            }
        }
        return $result;
    }

    /**
     * @param string $inputName
     * @return string
     */
    public function getAttributeDisplayName($inputName)
    {
        $validator = $this->getValidator();
        $rules = ValidatorWrapper::fetchInitialRules($validator);

        foreach ($rules as $attribute => $rules) {
            if ($this->isAttributeMatchesInputName($attribute, $inputName)) {
                return ValidatorWrapper::generateAttributeDisplayName($validator, $attribute);
            }
        }
        return ValidatorWrapper::generateAttributeDisplayName($validator, $inputName);
    }

    /**
     * Normalizes input name.
     * @see \Collective\Html\FormBuilder::transformKey()
     * @see \Illuminate\Validation\Validator::passes()
     *
     * @param string $attribute
     * @param string $inputName
     * @return string string
     */
    protected function isAttributeMatchesInputName($attribute, $inputName)
    {
        $normalizedInputName = str_replace(['.', '[]', '[', ']'], ['\.', '.*', '.', ''], $inputName);
        return Str::is($attribute, $normalizedInputName);
    }

    /**
     * @param array $laravelRules
     * @return bool
     */
    protected function containsNumericRule(array $laravelRules)
    {
        $validator = $this->getValidator();
        $numericRules = ValidatorWrapper::fetchNumericRulesNames($validator);
        $currentRules = array_column($laravelRules, 0);
        return (bool) count(array_intersect($currentRules, $numericRules));
    }

    /**
     * @param array $laravelRules
     * @return bool
     */
    protected function containsFileRule(array $laravelRules)
    {
        $validator = $this->getValidator();
        $fileRules = ValidatorWrapper::fetchFileRulesNames($validator);
        $currentRules = array_column($laravelRules, 0);
        return (bool) count(array_intersect($currentRules, $fileRules));
    }

    /**
     * @param array $laravelRules
     * @return integer
     */
    protected function detectAttributeOptions(array $laravelRules)
    {
        $result = 0;
        if ($this->containsNumericRule($laravelRules)) {
            $result |= RuleConverter::OPTION_ATTRIBUTE_IS_NUMERIC;
        }
        if ($this->containsFileRule($laravelRules)) {
            $result |= RuleConverter::OPTION_ATTRIBUTE_IS_FILE;
        }
        return $result;
    }

    /**
     * @param RuleConverter[] $rules
     * @return RuleConverter[]
     */
    protected function removeInvalidDateRules(array $rules)
    {
        $hasAnyDateRule = false;
        $hasDateFormatRule = false;

        foreach ($rules as $rule) {
            if (!$hasAnyDateRule && $rule->isDateRule()) {
                $hasAnyDateRule = true;
            }
            if (!$hasDateFormatRule && array_key_exists('date_format', $rule->getVueRules())) {
                $hasDateFormatRule = true;
            }

            if ($hasAnyDateRule && $hasDateFormatRule) {
                break;
            }
        }

        if (!$hasAnyDateRule || $hasDateFormatRule) {
            return $rules;
        }
        return array_values(array_filter($rules, function (RuleConverter $rule) {
            return !$rule->isDateRule();
        }));
    }
}
