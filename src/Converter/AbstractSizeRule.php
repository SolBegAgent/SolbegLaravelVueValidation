<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class AbstractSizeRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
abstract class AbstractSizeRule extends AbstractRule
{
    /**
     * @return array
     * @throws \LogicException
     */
    abstract protected function generateFileVueRules();

    /**
     * @return array
     * @throws \LogicException
     */
    abstract protected function generateNumericVueRules();

    /**
     * @return array
     * @throws \LogicException
     */
    abstract protected function generateStringVueRules();

    /**
     * @return boolean
     */
    abstract protected function isValidForFileAttribute();

    /**
     * @return boolean
     */
    abstract protected function isValidForNumericAttribute();

    /**
     * @return boolean
     */
    abstract protected function isValidForStringAttribute();

    /**
     * @return array
     * @throws \LogicException
     */
    protected function generateArrayVueRules()
    {
        throw $this->createCannotGenerateRuleException('array');
    }

    /**
     * @return bool
     */
    protected function isValidForArrayAttribute()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getVueRules()
    {
        if ($this->isArrayAttribute()) {
            return $this->generateArrayVueRules();
        } elseif ($this->isFileAttribute()) {
            return $this->generateFileVueRules();
        } elseif ($this->isNumericAttribute()) {
            return $this->generateNumericVueRules();
        } else {
            return $this->generateStringVueRules();
        }
    }

    /**
     * @inheritdoc
     */
    public function isValid()
    {
        if ($this->isArrayAttribute()) {
            return $this->isValidForArrayAttribute();
        } elseif ($this->isFileAttribute()) {
            return $this->isValidForFileAttribute();
        } elseif ($this->isNumericAttribute()) {
            return $this->isValidForNumericAttribute();
        } else {
            return $this->isValidForStringAttribute();
        }
    }

    /**
     * @inheritdoc
     */
    public function isDateRule()
    {
        return false;
    }

    /**
     * @param string|null $attributeType
     * @return \LogicException
     */
    protected function createCannotGenerateRuleException($attributeType = null)
    {
        $message = "Cannot generate Vue rule for '{$this->getNormalizedLaravelRule()}' rule";
        if ($attributeType !== null) {
            $message .= " if it is $attributeType attribute";
        }
        return new \LogicException("$message.");
    }
}
