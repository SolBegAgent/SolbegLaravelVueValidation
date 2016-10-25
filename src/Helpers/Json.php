<?php

namespace Solbeg\VueValidation\Helpers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
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
     * @param integer|null $options
     * @return string the encoding result
     */
    public static function encode($data, $options = null)
    {
        if ($options === null) {
            $options = JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS;
        }

        if (is_scalar($data) || $data === null) {
            return json_encode($data, $options);
        }

        $expressions = [];
        $data = static::processData($data, $expressions, uniqid('', true), $options);

        $json = json_encode($data, $options);
        return $expressions ? strtr($json, $expressions) : $json;
    }

    /**
     * @param mixed $data
     * @param array $expressions
     * @param string $expPrefix
     * @param integer $options
     * @return mixed
     */
    protected static function processData($data, &$expressions = [], $expPrefix, $options)
    {
        if (is_object($data)) {
            if ($data instanceof Collection) {
                $data = $data->all();
            } elseif ($data instanceof Arrayable) {
                $data = $data->toArray();
            } elseif ($data instanceof \JsonSerializable) {
                $data = $data->jsonSerialize();
            } elseif ($data instanceof \Traversable) {
                $data = iterator_to_array($data);
            } elseif ($data instanceof \SimpleXMLElement) {
                $data = (array) $data;
            } elseif ($data instanceof Jsonable) {
                $token = "!{[$expPrefix=". count($expressions) . ']}!';
                $expressions['"' . $token . '"'] = $data->toJson($options);
                return $token;
            } else {
                $result = [];
                foreach ($data as $name => $value) {
                    $result[$name] = $value;
                }
                $data = $result;
            }

            if ($data === []) {
                return new \stdClass();
            }
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $data[$key] = static::processData($value, $expressions, $expPrefix, $options);
                }
            }
        }

        return $data;
    }
}
