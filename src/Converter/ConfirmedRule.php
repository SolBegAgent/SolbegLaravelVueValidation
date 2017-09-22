<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class ConfirmedRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class ConfirmedRule extends AbstractRule
{
    /**
     * @var string
     */
    public static $targetSuffix = '_confirmation';

    /**
     * @inheritdoc
     * @throws \LogicException
     */
    public function getVueRules()
    {
        $params = $this->getLaravelParams();

        if ($params) {
            $target = reset($params);
            $params = [$this->convertTargetName($target)];
        } else {
            $params = [$this->generateTargetName()];
        }

        $rule = $this->getNormalizedLaravelRule();
        if ($rule !== 'confirmed') {
            $rule = 'same';
        }

        return [$rule => $params];
    }

    /**
     * @inheritdoc
     */
    public function isValid()
    {
        return $this->getLaravelParams() || $this->isValidInputName();
    }

    /**
     * @inheritdoc
     */
    public function isDateRule()
    {
        return false;
    }

    /**
     * @return string
     * @throws \LogicException
     */
    protected function generateTargetName()
    {
        $inputName = $this->getInputName();

        if (!$this->isValidInputName()) {
            throw new \LogicException("Cannot generate target input name for array input: '$inputName'.");
        } elseif (preg_match('/\[[^\]]+\]$/', $inputName)) {
            return preg_replace_callback('/\[([^\]]+)\]$/', function (array $matches) {
                return '[' . $matches[1] . self::$targetSuffix . ']';
            }, $inputName, 1);
        } else {
            return $inputName . self::$targetSuffix;
        }
    }

    /**
     * @return bool
     */
    protected function isValidInputName()
    {
        return !$this->isArrayAttribute();
    }

    /**
     * @param string $laravelTargetName
     * @return string target name for Vue `confirmed` rule.
     */
    protected function convertTargetName($laravelTargetName)
    {
        return preg_replace_callback('/\.([^\.]+)/', function (array $matches) {
            return "[$matches[1]]";
        }, $laravelTargetName);
    }
}
