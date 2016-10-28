<?php

namespace Solbeg\VueValidation;

use Collective\Html\HtmlBuilder as BaseHtmlBuilder;

/**
 * Class HtmlBuilder
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class HtmlBuilder extends BaseHtmlBuilder
{
    /**
     * @inheritdoc
     */
    protected function attributeElement($key, $value)
    {
        if (!is_bool($value)) {
            return parent::attributeElement($key, $value);
        } elseif ($value) {
            return (string) $key;
        }
    }

    /**
     * @param string $jsCode
     * @param array $attributes
     * @param boolean $onReady
     * @return \Illuminate\Support\HtmlString
     */
    public function jsCode($jsCode, $attributes = [], $onReady = true)
    {
        if (!array_key_exists('type', $attributes)) {
            $attributes['type'] = 'text/javascript';
        }

        if ($onReady) {
            $jsCode = "document.addEventListener(\"DOMContentLoaded\", function(){\n$jsCode\n});";
        }

        return $this->toHtmlString(implode(PHP_EOL, [
            '<script' . $this->attributes($attributes) . '>',
            $jsCode,
            '</script>',
        ]) . PHP_EOL);
    }

    /**
     * @see \Solbeg\VueValidation\Helpers\Json::encode()
     * @param mixed $data
     * @param integer|null $options
     * @return string
     */
    public function encodeJs($data, $options = null)
    {
        return Helpers\Json::encode($data, $options);
    }
}
