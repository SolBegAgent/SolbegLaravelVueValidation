<?php

namespace Solbeg\VueValidation\Converter;

use Solbeg\VueValidation\Helpers\MomentJsFormatConverter;

/**
 * Class DateFormatRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class DateFormatRule extends AbstractRule
{
    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function getVueRules()
    {
        $params = $this->getVueDateParams();
        return ['date_format' => $params];
    }

    /**
     * @inheritdoc
     * @throws \LogicException
     */
    public function isValid()
    {
        $params = $this->requireLaravelParams(1);
        try {
            return (bool) $this->convertDateFormat($params[0]);
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function isDateRule()
    {
        return true;
    }

    /**
     * @return array
     * @throws \Exception
     * @throws \LogicException
     */
    protected function getVueDateParams()
    {
        $params = $this->requireLaravelParams(1);
        $params[0] = $this->convertDateFormat($params[0]);
        if ($params[0] === false) {
            throw new \LogicException("A valid format string is required as parameter for {$this->getNormalizedLaravelRule()} validation rule.");
        }
        return $params;
    }

    /**
     * @param string $phpFormat
     * @return string|false
     * @throws \Exception
     */
    protected function convertDateFormat($phpFormat)
    {
        $result = MomentJsFormatConverter::convert($phpFormat);
        if (!is_string($result) || $result === '' || strpos($result, '|') !== false || strpos($result, ',') !== false) {
            return false;
        }
        return $result;
    }
}
