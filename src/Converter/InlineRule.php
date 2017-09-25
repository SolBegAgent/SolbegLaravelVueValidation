<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class InlineRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class InlineRule extends AbstractRule
{
    const KEY_IS_DATE_RULE = 'isDateRule';

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var array|false|null
     */
    private $callbackResult;

    /**
     * InlineRule constructor.
     * @param callable $callback
     * @param string $inputName
     * @param string $attribute
     * @param string $message
     * @param string $rule
     * @param array $params
     * @param integer $attributeOptions
     */
    public function __construct(callable $callback, $inputName, $attribute, $message, $rule, array $params = [], $attributeOptions = 0)
    {
        $this->callback = $callback;
        parent::__construct($inputName, $attribute, $message, $rule, $params, $attributeOptions);
    }

    /**
     * @inheritdoc
     * @throws \BadMethodCallException
     * @throws \BadFunctionCallException
     */
    public function getVueRules()
    {
        $result = $this->invokeCallback();
        if (!is_array($result)) {
            throw new \BadMethodCallException("Cannot generate Vue rules for '{$this->getNormalizedLaravelRule()}' rule.");
        }

        unset($result[self::KEY_IS_DATE_RULE]);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function isDateRule()
    {
        $result = $this->invokeCallback();
        return (is_array($result) && isset($result[self::KEY_IS_DATE_RULE]))
            ? (bool) $result[self::KEY_IS_DATE_RULE]
            : false;
    }

    /**
     * @inheritdoc
     */
    public function isValid()
    {
        return $this->invokeCallback() !== false;
    }

    /**
     * @return array
     * @throws \BadFunctionCallException
     */
    public function invokeCallback()
    {
        if ($this->callbackResult !== null) {
            return $this->callbackResult;
        }

        $rule = $this->getLaravelRule();
        $params = $this->getLaravelParams();

        $result = call_user_func($this->callback, $rule, $params, $this);
        if ($result !== false && !is_array($result)) {
            throw new \BadFunctionCallException('Invalid result of inline converter. It must be FALSE or an array with Vue rules.');
        }
        return $this->callbackResult = $result;
    }
}
