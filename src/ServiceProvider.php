<?php

namespace Solbeg\VueValidation;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * Class ServiceProvider
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class ServiceProvider extends BaseServiceProvider
{
    const SERVICE_FORM = 'vue-validation::form';
    const SERVICE_HTML = 'vue-validation::html';

    /**
     * @inheritdoc
     */
    protected $defer = true;

    /**
     * Registers services.
     */
    public function register()
    {
        $this->registerHtmlBuilder();
        $this->registerFormBuilder();
    }

    /**
     * Registers form builder.
     */
    protected function registerFormBuilder()
    {
        $this->app->singleton(self::SERVICE_FORM, function ($app) {
            $form = new Form(
                $app->make(self::SERVICE_HTML),
                $app->make('url'),
                $app->make('view'),
                $app['session.store']->getToken()
            );
            $form->setSessionStore($app['session.store']);
            $form->setContainer($app);
            return $form;
        });
    }

    /**
     * Registers html builder.
     */
    protected function registerHtmlBuilder()
    {
        $this->app->singleton(self::SERVICE_HTML, function ($app) {
            return new HtmlBuilder($app->make('url'), $app->make('view'));
        });
    }

    /**
     * @inheritdoc
     */
    public function provides()
    {
        return [
            self::SERVICE_FORM,
            self::SERVICE_HTML,
        ];
    }
}
