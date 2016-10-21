<?php

namespace Solbeg\VueValidation\Wrappers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

/**
 * Class FormRequestWrapper
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class FormRequestWrapper extends FormRequest
{
    /**
     * @param FormRequest $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function fetchValidatorInstance(FormRequest $request)
    {
        return $request->getValidatorInstance();
    }

    /**
     * @param $requestClass
     * @param Request $sourceRequest
     * @param Container $container
     * @return FormRequest
     */
    public static function instantiateRequest($requestClass, Request $sourceRequest, Container $container)
    {
        /* @var $requestClass FormRequest */
        $request = $requestClass::createFromBase($sourceRequest);

        if ($session = $sourceRequest->getSession()) {
            $request->setSession($session);
        }

        $request->setUserResolver($sourceRequest->getUserResolver());
        $request->setRouteResolver($sourceRequest->getRouteResolver());

        $request->setContainer($container);
        $request->setRedirector($container->make(Redirector::class));

        return $request;
    }
}
