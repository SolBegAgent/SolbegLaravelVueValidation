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
}
