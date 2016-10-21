<?php

namespace Solbeg\VueValidation\Converter;

use Solbeg\VueValidation\Wrappers\ValidatorWrapper;

/**
 * Class CompareDateRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class CompareDateRule extends SimpleRule
{
    /**
     * @inheritdoc
     */
    public function isValid()
    {
        $params = $this->requireLaravelParams(1);
        return parent::isValid() && !strtotime($params[0]);
    }

    /**
     * @inheritdoc
     */
    public function isDateRule()
    {
        return true;
    }
}
