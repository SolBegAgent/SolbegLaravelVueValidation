<?php
/**
 * Bootstrapper Form facade
 */

namespace Bootstrapper;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for Form
 *
 * @package Bootstrapper\Facades
 * @see     Bootstrapper\Form
 */
class FacadesForm extends Facade
{
    const FORM_HORIZONTAL = 'form-horizontal';
    const FORM_INLINE = 'form-inline';
    const FORM_SUCCESS = 'has-success';
    const FORM_WARNING = 'has-warning';
    const FORM_ERROR = 'has-error';
    const INPUT_LARGE = 'input-lg';

    /**
     * {@inheritdoc}
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'bootstrapper::form';
    }
}
