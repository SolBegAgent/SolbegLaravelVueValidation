<?php

namespace Solbeg\VueValidation;

use Bootstrapper\Form as BaseForm;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class Form
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class Form extends BaseForm
{
    /**
     * The current form request instance for the form.
     *
     * @var FormRequest|null
     */
    private $request;

    /**
     * @inheritdoc
     */
    public function __construct(HtmlBuilder $html, UrlGenerator $url, Factory $view, $csrfToken)
    {
        if (!in_array('request', $this->reserved ?: [], true)) {
            $this->reserved[] = 'request';
        }

        parent::__construct($html, $url, $view, $csrfToken);
    }

    /**
     * @param array $options
     * @return \Illuminate\Support\HtmlString
     */
    public function open(array $options = [])
    {
        if (isset($options['request'])) {
            $this->setRequest($options['request']);
        }

        return parent::open($options);
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        $result = parent::close();
        $this->setRequest(null);
        return $result;
    }

    /**
     * @param FormRequest $request
     * @param array $options
     * @return \Illuminate\Support\HtmlString
     */
    public function request($request, $options = [])
    {
        $this->setRequest($request);
        return $this->open($options);
    }

    /**
     * @param FormRequest $request
     * @param array $options
     * @return string
     */
    public function inlineRequest($request, $options = [])
    {
        $this->setRequest($request);
        return $this->inline($options);
    }

    /**
     * @param FormRequest $request
     * @param array $options
     * @return string
     */
    public function horizontalRequest($request, $options = [])
    {
        $this->setRequest($request);
        return $this->horizontal($options);
    }

    /**
     * @param mixed $model
     * @param FormRequest $request
     * @param array $options
     * @return string
     */
    public function inlineModelRequest($model, $request, $options = [])
    {
        $this->setRequest($request);
        return $this->inlineModel($model, $options);
    }

    /**
     * @param mixed $model
     * @param FormRequest $request
     * @param array $options
     * @return string
     */
    public function horizontalModelRequest($model, $request, $options = [])
    {
        $this->setRequest($request);
        return $this->horizontalModel($model, $options);
    }

    /**
     * @return FormRequest|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param FormRequest|null $request
     * @return static $this
     */
    public function setRequest(FormRequest $request = null)
    {
        $this->request = $request;
        return $this;
    }
}
