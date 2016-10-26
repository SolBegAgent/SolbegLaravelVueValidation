<?php

namespace Solbeg\VueValidation\Converter;

use Solbeg\VueValidation\Helpers\JsRegexpConverter;

/**
 * Class RegexRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class RegexRule extends AbstractRule
{
    /**
     * @var array|false|null
     */
    private $jsRegexp = null;

    /**
     * @inheritdoc
     */
    public function getVueRules()
    {
        $jsRegexp = $this->getJsRegexp();
        if ($jsRegexp !== false) {
            return ['regex' => $jsRegexp];
        }

        $params = $this->requireLaravelParams(1);
        throw new \LogicException(implode(' ', [
            "Cannot generate Vue '{$this->getNormalizedLaravelRule()}' rule for PHP pattern: $params[0].",
            'You cannot use "/" and "," chars in your pattern.',
        ]));
    }

    /**
     * @inheritdoc
     */
    public function isValid()
    {
        return $this->getJsRegexp() !== false;
    }

    /**
     * @inheritdoc
     */
    public function isDateRule()
    {
        return false;
    }

    /**
     * @return array|false
     */
    protected function getJsRegexp()
    {
        if ($this->jsRegexp !== null) {
            return $this->jsRegexp;
        }

        $params = $this->requireLaravelParams(1);
        $jsRegexp = JsRegexpConverter::convert($params[0], true);

        if (array_key_exists(1, $jsRegexp) && !strlen($jsRegexp[1])) {
            unset($jsRegexp[1]);
        }

        if (strpos($jsRegexp[0], '|') !== false || strpos($jsRegexp[0], ',') !== false) {
            $jsRegexp = false;
        }
        return $this->jsRegexp = $jsRegexp;
    }
}
