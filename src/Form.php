<?php

namespace Solbeg\VueValidation;

use Bootstrapper\Form as BaseForm;
use Bootstrapper\Facades\ControlGroup;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\ViewErrorBag;

use Solbeg\VueValidation\Helpers\IdGenerator;
use Solbeg\VueValidation\Helpers\Json;

/**
 * Class Form
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class Form extends BaseForm
{
    /**
     * @var string
     */
    public static $controlGroupErrorClass = 'has-error';

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
     * @var string|null
     */
    private $formId;

    /**
     * @var Contracts\RuleConverter[]
     */
    private $parsedRules = [];

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

            if (!isset($options['id'])) {
                $options['id'] = IdGenerator::generateId($this);
            }
        }

        if (isset($options['id'])) {
            $this->setFormId($options['id']);
        }

        return parent::open($options);
    }

    /**
     * @param array $vueOptions
     * @inheritdoc
     */
    public function close(array $vueOptions = [])
    {
        $result = parent::close();

        $vueJs = $this->renderVueJs($vueOptions);
        if ($vueJs !== null) {
            $result = $this->toHtmlString(implode(PHP_EOL, [
                (string) $result,
                (string) $this->html->jsCode($vueJs),
            ]));
        }

        $this->setFormId(null);
        $this->setRequest(null);
        $this->clearParsedRules();
        return $result;
    }

    /**
     * @param array $options
     * @return string|null
     * @throws \LogicException
     */
    public function renderVueJs(array $options = [])
    {
        if (!$this->getRequest()) {
            return null;
        }

        $formId = $this->getFormId();
        if ($formId === null) {
            throw new \LogicException('Cannot render Vue JS code until form ID will be defined.');
        }

        $jsOptions = Json::encode(array_merge([
            'el' => "#$formId",
        ], $options));

        $jsMessages = Json::encode($this->generateAllVueMessages());
        $jsBackendErrors = Json::encode($this->generateAllLaravelMessages() ?: new \stdClass);
        return "solbeg.initVueValidation($jsOptions, $jsMessages, $jsBackendErrors);";
    }

    /**
     * @return array
     */
    protected function generateAllLaravelMessages()
    {
        $errors = $this->getRequest()->getSession()->get('errors') ?: new \Illuminate\Support\ViewErrorBag();
        $result = [];
        foreach ($errors->keys() as $field) {
            $result[$field] = $errors->first($field);
        }
        return $result;
    }

    /**
     * @return array[]
     */
    protected function generateAllVueMessages()
    {
        $result = [];

        foreach ($this->getParsedRules() as $rule) {
            $inputName = $rule->getInputName();
            $message = $rule->getMessage();

            foreach ($rule->getVueRules() as $vueRule => $vueParams) {
                $vueParams = array_values(array_map('strval', $vueParams));
                $result[] = [$inputName, $vueRule, $vueParams, $message];
            }
        }

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
                $this->addParsedRules($rules);
            }
        }

        if (isset($options['data-rules']) && !isset($options['v-validate'])) {
            $options['v-validate'] = true;
        }

        return parent::input($type, $name, $value, $options);
    }

    /**
     * @param string $name input name
     * @param array $attributes
     * @return \Illuminate\Support\HtmlString
     */
    public function vueError($name, array $attributes = [])
    {
        $jsEncodedName = Json::encode($name);
        $label = "{{ errors.first($jsEncodedName) }}";

        if (!array_key_exists('v-show', $attributes)) {
            $attributes['v-show'] = "errors.has($jsEncodedName)";
        }
        if (!array_key_exists('style', $attributes)) {
            $attributes['style'] = 'display:none;';
        }

        return $this->help($label, $attributes);
    }

    /**
     * Generates a full control group with a label, control and help block
     *
     * @param string $inputName
     * @param string $label The label
     * @param string $control The form control
     * @param string $help The help text
     * @param int $labelSize The size of the label
     * @param int $controlSize The size of the form control
     * @return \Bootstrapper\ControlGroup
     * @throws \Bootstrapper\Exceptions\ControlGroupException
     */
    public function controlGroup($inputName, $label, $control, $help = null, $labelSize = null, $controlSize = null)
    {
        if ($this->getRequest() && $help === null) {
            $help = $this->vueError($inputName);
            $addVueClass = true;
        }

        $result = ControlGroup::generate(
            $label,
            $control,
            $help,
            $labelSize,
            $controlSize
        );
        /* @var $result \Bootstrapper\ControlGroup */

        if (!empty($addVueClass)) {
            $jsErrorClass = Json::encode(self::$controlGroupErrorClass);
            $jsInputName = Json::encode($inputName);
            $result->withAttributes([
                ':class' => "{{$jsErrorClass}: errors.has($jsInputName)}",
            ]);
        }

        return $result;
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
     * @return string|null
     */
    public function getFormId()
    {
        return $this->formId;
    }


    /**
     * @param string|null $formId
     * @return static $this
     */
    public function setFormId($formId)
    {
        $this->formId = $formId;
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

    /**
     * @param Contracts\RuleConverter[]|Contracts\RuleConverter $rules
     * @return static $this
     */
    public function addParsedRules($rules)
    {
        $this->parsedRules = array_merge($this->parsedRules, (array) $rules);
        return $this;
    }

    /**
     * @return static $this
     */
    public function clearParsedRules()
    {
        $this->parsedRules = [];
        return $this;
    }

    /**
     * @return Contracts\RuleConverter[]
     */
    public function getParsedRules()
    {
        return $this->parsedRules;
    }
}
