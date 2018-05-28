<?php
/**
 * Bootstrapper Service Provider
 */

namespace Solbeg\VueValidation\Bootstrapper;

use Collective\Html\HtmlBuilder;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for Laravel
 *
 * @package Bootstrapper
 */
class BootstrapperL5ServiceProvider extends ServiceProvider
{

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerButton();
        $this->registerControlGroup();
        $this->registerFormBuilder();
    }

    
    /**
     * Registers the Button class into the IoC
     */
    private function registerButton()
    {
        $this->app->bind(
            'solbeg::vue-validation::bootstrapper::button',
            function () {
                return new Button;
            }
        );
    }

    /**
     * Registers the ControlGroup class into the IoC
     */
    private function registerControlGroup()
    {
        $this->app->bind(
            'solbeg::vue-validation::bootstrapper::controlgroup',
            function ($app) {
                return new ControlGroup($app['solbeg::vue-validation::bootstrapper::form']);
            }
        );
    }

    /**
     * Registers the FormBuilder class into the IoC
     */
    private function registerFormBuilder()
    {
        $this->app->bind(
            'collective::html',
            function ($app) {
                return new HtmlBuilder($app->make('url'), $app->make('view'));
            }
        );
        $this->app->bind(
            'solbeg::vue-validation::bootstrapper::form',
            function ($app) {
                $tokenMethod = method_exists($app['session.store'], 'getToken') ? 'getToken' : 'token';
                $form = new Form(
                    $app->make('collective::html'),
                    $app->make('url'),
                    $app->make('view'),
                    $app['session.store']->$tokenMethod()
                );

                return $form->setSessionStore($app['session.store']);
            },
            true
        );
    }
    
}
