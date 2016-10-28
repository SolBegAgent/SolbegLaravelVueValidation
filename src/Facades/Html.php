<?php

namespace Solbeg\VueValidation\Facades;

use Illuminate\Support\Facades\Facade;
use Solbeg\VueValidation\ServiceProvider;

/**
 * Class Html
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class Html extends Facade
{
    /**
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return ServiceProvider::SERVICE_HTML;
    }
}
