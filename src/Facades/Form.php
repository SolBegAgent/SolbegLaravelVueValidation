<?php

namespace Solbeg\VueValidation\Facades;

use Bootstrapper\Facades\Form as BaseFormFacade;
use Solbeg\VueValidation\ServiceProvider;

/**
 * Class Form
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class Form extends BaseFormFacade
{
    /**
     * {@inheritdoc}
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ServiceProvider::SERVICE_FORM;
    }
}
