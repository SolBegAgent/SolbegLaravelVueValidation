<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class DimensionsRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class DimensionsRule extends AbstractRule
{
    /**
     * @inheritdoc
     * @throws \BadMethodCallException
     */
    public function getVueRules()
    {
        $params = $this->getLaravelParams();
        if (!isset($params['width'], $params['height'])) {
            throw new \BadMethodCallException("Cannot generate Vue rule for '{$this->getNormalizedLaravelRule()}' rule until width & height will not be defined.");
        }

        return ['dimensions' => [
            $params['width'],
            $params['height'],
        ]];
    }

    /**
     * @inheritdoc
     */
    public function isValid()
    {
        $params = $this->getLaravelParams();
        return isset($params['width'], $params['height']);
    }

    /**
     * @inheritdoc
     */
    public function isDateRule()
    {
        return false;
    }
}
