<?php

namespace Solbeg\VueValidation\Helpers;

use Illuminate\Contracts\Support\Jsonable;

/**
 * Class JsExpression marks a string as a JavaScript expression.
 *
 * When using Json::encode() to encode a value, JsonExpression objects
 * will be specially handled and encoded as a JavaScript expression instead of a string.
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class JsExpression implements Jsonable
{
    /**
     * @var string
     */
    private $expression;

    /**
     * JsExpression constructor.
     * @param string $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return (string) $this->getExpression();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
