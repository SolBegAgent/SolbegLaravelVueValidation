<?php
/**
 * Bootstrapper ControlGroup facade
 */

namespace Solbeg\VueValidation\Bootstrapper\Facades;

/**
 * Facade for Control Groups
 *
 * @package Bootstrapper\Facades
 * @see     Solbeg\VueValidation\Bootstrapper\ControlGroup
 */
class ControlGroup extends BootstrapperFacade
{

    /**
     * {@inheritdoc}
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'solbeg::vue-validation::bootstrapper::controlgroup';
    }
}
