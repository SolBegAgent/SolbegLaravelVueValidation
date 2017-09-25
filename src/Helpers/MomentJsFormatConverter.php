<?php

namespace Solbeg\VueValidation\Helpers;

/**
 * Class MomentJsFormatConverter
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class MomentJsFormatConverter
{
    /**
     * @var array
     */
    public static $replacements = [
        'd' => 'DD',
        'D' => 'ddd',
        'j' => 'D',
        'l' => 'dddd',
        'N' => 'E',
        'S' => 'o',
        'w' => 'e',
        'z' => 'DDD',
        'W' => 'W',
        'F' => 'MMMM',
        'm' => 'MM',
        'M' => 'MMM',
        'n' => 'M',
        't' => null, // no equivalent
        'L' => null, // no equivalent
        'o' => 'YYYY',
        'Y' => 'YYYY',
        'y' => 'YY',
        'a' => 'a',
        'A' => 'A',
        'B' => null, // no equivalent
        'g' => 'h',
        'G' => 'H',
        'h' => 'hh',
        'H' => 'HH',
        'i' => 'mm',
        's' => 'ss',
        'u' => 'SSS',
        'e' => null, //'zz', deprecated since version 1.6.0 of moment.js
        'I' => null, // no equivalent
        'O' => null, // no equivalent
        'P' => null, // no equivalent
        'T' => null, // no equivalent
        'Z' => null, // no equivalent
        'c' => null, // no equivalent
        'r' => null, // no equivalent
        'U' => 'X',
        '[' => '[[]',
        ']' => '[]]',
    ];

    /**
     * @param $phpDateFormat
     * @return string
     * @throws \Exception
     * @internal param string $phpFormat
     */
    public static function convert($phpDateFormat)
    {
        $parts = preg_split('/(\\\\.)/u', $phpDateFormat, null, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $key => $part) {
            if (strncmp($part, '\\', 1) === 0) {
                $parts[$key] = '[' . substr($part, 1) . ']';
            } else {
                $parts[$key] = static::replaceChars($part);
            }
        }
        return implode('', $parts);
    }

    /**
     * @param string $str
     * @return string
     * @throws \Exception
     */
    protected static function replaceChars($str)
    {
        foreach (static::getInvalidChars() as $char) {
            if (mb_strpos($str, $char, 0, 'UTF-8') !== false) {
                throw new \RuntimeException("Cannot convert PHP date format '$str' to MomentJs format, because the last has not an equivalent for '$char'.");
            }
        }
        return strtr($str, self::$replacements);
    }

    /**
     * @return string[]
     */
    protected static function getInvalidChars()
    {
        static $result = null;
        if ($result === null) {
            $result = array_keys(array_filter(self::$replacements, 'is_null'));
        }
        return $result;
    }
}
