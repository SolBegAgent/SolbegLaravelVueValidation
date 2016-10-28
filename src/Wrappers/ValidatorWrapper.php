<?php

namespace Solbeg\VueValidation\Wrappers;

use Illuminate\Validation\Validator;

/**
 * Class ValidatorWrapper
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class ValidatorWrapper extends Validator
{
    /**
     * @var string[]
     */
    public static $rulesWithNamedParams = [
        'Dimensions',
    ];

    /**
     * @param Validator $validator
     * @return array
     */
    public static function fetchInitialRules(Validator $validator)
    {
        return $validator->initialRules;
    }

    /**
     * @param Validator $validator
     * @param mixed $rules
     * @return array[]
     */
    public static function extractRulesWithParams(Validator $validator, $rules)
    {
        /* @see Validator::explodeRules() */
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        } elseif (!is_array($rules)) {
            $rules = [$rules];
        }

        $result = [];
        foreach ($rules as $rule) {
            list($rule, $params) = $validator->parseRule($rule);
            if ($rule != '') {
                $result[] = [$rule, static::normalizeRuleParams($validator, $rule, $params)];
            }
        }

        return $result;
    }

    /**
     * @param Validator $validator
     * @param string $rule
     * @param array $params
     * @return array
     */
    protected static function normalizeRuleParams(Validator $validator, $rule, $params)
    {
        if (in_array($rule, self::$rulesWithNamedParams, true)) {
            return $validator->parseNamedParameters($params);
        }
        return $params;
    }

    /**
     * @param Validator $validator
     * @return array
     */
    public static function fetchNumericRulesNames(Validator $validator)
    {
        return $validator->numericRules;
    }

    /**
     * @param Validator $validator
     * @return array
     */
    public static function fetchFileRulesNames(Validator $validator)
    {
        $numericRules = static::fetchNumericRulesNames($validator);
        $result = array_diff($validator->fileRules, $validator->sizeRules, $numericRules);
        return array_values($result);
    }

    /**
     * @see Validator::addError()
     *
     * @param Validator $validator
     * @param string $attribute
     * @param string $rule
     * @param array $params
     * @return string
     */
    public static function generateErrorMessage(Validator $validator, $attribute, $rule, array $params = [])
    {
        $message = $validator->getMessage($attribute, $rule);
        return $validator->doReplacements($message, $attribute, $rule, $params);
    }

    /**
     * @see Validator::getAttribute()
     *
     * @param Validator $validator
     * @param string $attribute
     */
    public static function generateAttributeDisplayName(Validator $validator, $attribute)
    {
        return $validator->getAttribute($attribute);
    }
}
