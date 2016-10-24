<?php

namespace Solbeg\VueValidation\Helpers;

use Illuminate\Support\Collection;

/**
 * Json class provides some helper methods for working with JS expressions.
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class Json
{
    /**
     * Encodes the given value into a JSON string HTML-escaping entities so it is safe to be embedded in HTML code.
     * The method enhances `json_encode()` by supporting JavaScript expressions.
     *
     * @param mixed $data the data to be encoded
     * @return string the encoding result
     */
    public static function htmlEncode($data)
    {
        $options = JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS;
        if (is_scalar($data) || $data === null)
        {
            return json_encode($data, $options);
        }
        // Encoding through Collection for converting all special objects like Jsonable, JsonSerializable, etc...
        return (new Collection($data))->toJson($options);
    }
}
