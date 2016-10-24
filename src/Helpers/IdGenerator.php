<?php

namespace Solbeg\VueValidation\Helpers;

use Illuminate\Support\Str;

/**
 * Class IdGenerator
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class IdGenerator
{
    /**
     * @var integer[]
     */
    private static $counters = [];

    /**
     * @param object|string|null $caller
     * @return string
     */
    public static function generateId($caller = null)
    {
        $normalizedCaller = static::normalizeCaller($caller);
        $count = static::incrementCount($normalizedCaller);
        return Str::slug(str_replace('\\', ' ', $normalizedCaller) . " $count", '-');
    }

    /**
     * @param object|string|null $caller
     * @return int
     */
    protected static function incrementCount($caller = null)
    {
        $normalizedCaller = static::normalizeCaller($caller);

        if (!isset(self::$counters[$normalizedCaller])) {
            self::$counters[$normalizedCaller] = 0;
        }

        return ++self::$counters[$normalizedCaller];
    }

    /**
     * @param object|string|null $caller
     * @return string
     */
    protected static function normalizeCaller($caller = null)
    {
        if ($caller === null) {
            return static::class;
        } elseif (is_object($caller)) {
            return get_class($caller);
        }
        return $caller;
    }
}
