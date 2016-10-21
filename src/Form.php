<?php

namespace Solbeg\VueValidation;

use Bootstrapper\Form as BaseForm;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory;
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
     * @var Container
     */
    private $container;

    /**
     * @var RulesParser
     */
    private $rulesParser;

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
     * @param string|FormRequest $request
     * @param array $options
     * @return \Illuminate\Support\HtmlString
     */
    public function request($request, $options = [])
    {
        $this->setRequest($request);
        return $this->open($options);
    }

    /**
     * @param string|FormRequest $request
     * @param array $options
     * @return string
     */
    public function inlineRequest($request, $options = [])
    {
        $this->setRequest($request);
        return $this->inline($options);
    }

    /**
     * @param string|FormRequest $request
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
     * @param string|FormRequest $request
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
     * @param string|FormRequest $request
     * @param array $options
     * @return string
     */
    public function horizontalModelRequest($model, $request, $options = [])
    {
        $this->setRequest($request);
        return $this->horizontalModel($model, $options);
    }

    /**
     * @inheritdoc
     */
    public function input($type, $name, $value = null, $options = [])
    {
        if ($this->getRequest()) {
            if ($rules = $this->getRulesParser()->generateVueRules($name)) {
                $options['data-rules'] = implode('|', $rules);
            }
        }

        if (isset($options['data-rules']) && !isset($options['v-validate'])) {
            $options['v-validate'] = true;
        }


        return parent::input($type, $name, $value, $options);
    }

    /**
     * @return FormRequest|null
     */
    public function getRequest()
    {
        if (is_string($this->request)) {
            $container = $this->getContainer();
            $this->request = Wrappers\FormRequestWrapper::instantiateRequest($this->request, $container['request'], $container);
        }
        return $this->request;
    }

    /**
     * @param string|FormRequest|null $request
     * @return static $this
     * @throws \InvalidArgumentException
     */
    public function setRequest($request)
    {
        if ($request !== null && !is_a($request, FormRequest::class, true)) {
            $requestClass = !is_object($request)
                ? is_string($request) ? $request : gettype($request)
                : get_class($request);
            throw new \InvalidArgumentException("Invalid request type: '$requestClass', it must be an instance of " . FormRequest::class . '.');
        }

        $this->request = $request;
        $this->rulesParser = null;
        return $this;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Container $container
     * @return static $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @return RulesParser
     */
    public function getRulesParser()
    {
        if ($this->rulesParser === null) {
            $this->rulesParser = $this->getContainer()->make(RulesParser::class, [
                'request' => $this->getRequest(),
            ]);
        }
        return $this->rulesParser;
    }
}
