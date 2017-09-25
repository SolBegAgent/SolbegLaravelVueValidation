<?php

namespace Solbeg\VueValidation\Helpers;

/**
 * Class JsRegexpConverter
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class JsRegexpConverter
{
    /**
     * Escapes regular expression to use in JavaScript
     * @param string $phpRegexp the PHP regular expression to be escaped.
     * @param boolean $asArray if true then array will be returned with two elements:
     *  0: JS regular expression without `/`
     *  1: regexp flags
     * @return string|array
     *
     * See `escapeJsRegularExpression()` method here:
     * @link https://github.com/yiisoft/yii2/blob/master/framework/helpers/BaseHtml.php
     */
    public static function convert($phpRegexp, $asArray = false)
    {
        $pattern = preg_replace('/\\\\x\{?([0-9a-fA-F]+)\}?/', '\u$1', $phpRegexp);
        $deliminator = $pattern[0];
        $pos = strrpos($pattern, $deliminator, 1);
        $flag = substr($pattern, $pos + 1);

        if ($deliminator !== '/') {
            $pattern = str_replace('/', '\\/', substr($pattern, 1, $pos - 1));
        } else {
            $pattern = substr($pattern, 1, $pos - 1);
        }

        $result = [
            $pattern,
            preg_replace('/[^igm]/', '', $flag),
        ];
        return !$asArray ? '/' . implode('/', $result) : $result;
    }
}
