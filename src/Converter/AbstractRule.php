<?php

namespace Solbeg\VueValidation\Converter;

use Illuminate\Support\Str;

use Solbeg\VueValidation\Contracts;
use Solbeg\VueValidation\Wrappers\ValidatorWrapper;

/**
 * Class AbstractRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
abstract class AbstractRule implements Contracts\RuleConverter
{
    use ToStringTrait;

    /**
     * @var string
     */
    private $inputName;

    /**
     * @var string
     */
    private $laravelRule;

    /**
     * @var array
     */
    private $laravelParams = [];

    /**
     * @var array
     */
    private $allLaravelRules = [];

    /**
     * @var integer
     */
    private $attributeOptions = 0;

    /**
     * @inheritdoc
     */
    abstract public function getVueRules();

    /**
     * @inheritdoc
     */
    abstract public function isValid();

    /**
     * @inheritdoc
     */
    abstract public function isDateRule();

    /**
     * AbstractRule constructor.
     *
     * @param string $inputName
     * @param string $rule
     * @param array $params
     * @param array $allRules
     * @param integer $attributeOptions
     */
    public function __construct($inputName, $rule, array $params = [], array $allRules = [], $attributeOptions = 0)
    {
        $this->inputName = $inputName;
        $this->laravelRule = $rule;
        $this->laravelParams = $params;
        $this->allLaravelRules = $allRules;
        $this->attributeOptions = $attributeOptions;
    }

    /**
     * @return string
     */
    public function getInputName()
    {
        return $this->inputName;
    }

    /**
     * @return string
     */
    public function getLaravelRule()
    {
        return $this->laravelRule;
    }

    /**
     * @return array
     */
    public function getLaravelParams()
    {
        return $this->laravelParams;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeOptions()
    {
        return $this->attributeOptions;
    }

    /**
     * @return boolean
     */
    public function isNumericAttribute()
    {
        return boolval($this->getAttributeOptions() & self::OPTION_ATTRIBUTE_IS_NUMERIC);
    }

    /**
     * @return boolean
     */
    public function isFileAttribute()
    {
        return boolval($this->getAttributeOptions() & self::OPTION_ATTRIBUTE_IS_FILE);
    }

    /**
     * @return boolean
     */
    public function isArrayAttribute()
    {
        return (bool) preg_match('/\[\]$/', $this->getInputName());
    }

    /**
     * @return string
     */
    protected function getNormalizedLaravelRule()
    {
        return Str::snake($this->getLaravelRule(), '_');
    }

    /**
     * @return array
     */
    public function getAllLaravelRules()
    {
        return $this->allLaravelRules;
    }

    /**
     * @param integer $count
     * @return array indexed Laravel params array
     * @throws \LogicException
     */
    protected function requireLaravelParams($count)
    {
        $params = $this->getLaravelParams();
        if (!is_array($params) || count($params) < $count) {
            throw new \LogicException("The '{$this->getNormalizedLaravelRule()}' rule must have at least $count params.");
        }
        return array_values($params);
    }

    /**
     * @param int|string $value
     * @return int
     * @throws \LogicException
     */
    protected function parseIntParam($value)
    {
        if (!is_scalar($value) || !preg_match('/\d+/', $value)) {
            $value = is_scalar($value) ? "'$value'" : gettype($value);
            throw new \LogicException("Invalid value of param  of the '{$this->getNormalizedLaravelRule()}' rule: $value. It must be an integer.");
        }
        return (int) $value;
    }
}
