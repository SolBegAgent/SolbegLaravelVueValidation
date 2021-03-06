<?php

namespace Solbeg\VueValidation;

use Solbeg\VueValidation\Bootstrapper\Form as BaseForm;
use Solbeg\VueValidation\Bootstrapper\Facades\ControlGroup;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\ViewErrorBag;

use Solbeg\VueValidation\Helpers\IdGenerator;
use Solbeg\VueValidation\Helpers\JsExpression;

/**
 * Class Form
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 *
 * @property HtmlBuilder $html
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
    protected $request;

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
     * @var string[]
     */
    private $vueScopes = [];

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
     * @throws \InvalidArgumentException
     */
    public function open(array $options = [])
    {
        if (isset($options['request'])) {
            $this->setRequest($options['request']);

            if (!isset($options['id'])) {
                $options['id'] = IdGenerator::generateId($this);
            }
            $options['data-scope'] = $options['id'];
            $this->beginVueScope($options['id']);

            if (!isset($options['v-form-validation'])) {
                $options['v-form-validation'] = $this->html->encodeJs(['prevent-submit' => true]);
            }
        }

        if (isset($options['id'])) {
            $this->setFormId($options['id']);
        }

        return parent::open($options);
    }

    /**
     * @param array|boolean $vueOptions
     * @inheritdoc
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function close($vueOptions = true)
    {
        $result = parent::close();

        $vueJs = $this->renderVueJs($vueOptions);
        if ($vueJs !== null) {
            $result = $this->toHtmlString(implode(PHP_EOL, [
                    (string) $result,
                    (string) $this->html->jsCode($vueJs),
            ]));
        }

        $this->clearVueScopes();
        $this->setFormId(null);
        $this->setRequest(null);
        $this->clearParsedRules();
        return $result;
    }

    /**
     * @param array|boolean $options
     * @return string|null
     * @throws \LogicException
     */
    public function renderVueJs($options = true)
    {
        if (!$this->getRequest() || in_array($options, [false, null], true)) {
            return null;
        } elseif ($options === true) {
            $options = [];
        }

        $formId = $this->getFormId();
        if ($formId === null) {
            throw new \LogicException('Cannot render Vue JS code until form ID will be defined.');
        }

        $jsOptions = $this->html->encodeJs(array_merge([
                'el' => "#$formId",
        ], $options));
        return "new Vue($jsOptions);";
    }

    /**
     * @param string|FormRequest $request
     * @param array $options
     * @return string
     * @throws \InvalidArgumentException
     */
    public function inlineRequest($request, array $options = [])
    {
        $this->setRequest($request);
        return $this->inline($options);
    }

    /**
     * @param string|FormRequest $request
     * @param array $options
     * @return string
     * @throws \InvalidArgumentException
     */
    public function horizontalRequest($request, array $options = [])
    {
        $this->setRequest($request);
        return $this->horizontal($options);
    }

    /**
     * @param mixed $model
     * @param string|FormRequest $request
     * @param array $options
     * @return string
     * @throws \InvalidArgumentException
     */
    public function inlineModelRequest($model, $request, array $options = [])
    {
        $this->setRequest($request);
        return $this->inlineModel($model, $options);
    }

    /**
     * @param mixed $model
     * @param string|FormRequest $request
     * @param array $options
     * @return string
     * @throws \InvalidArgumentException
     */
    public function horizontalModelRequest($model, $request, array $options = [])
    {
        $this->setRequest($request);
        return $this->horizontalModel($model, $options);
    }

    /**
     * @inheritdoc
     */
    public function input($type, $name, $value = null, $options = [])
    {
        $options = $this->prepareVueOptions($name, $options, __FUNCTION__ . ".$type");
        return parent::input($type, $name, $value, $options);
    }

    /**
     * @inheritdoc
     */
    public function select($name, $list = [], $selected = null, array $attributes = [], array $optionsAttributes = [], array $cap = [])
    {
        $attributes = $this->prepareVueOptions($name, $attributes, __FUNCTION__);
        return parent::select($name, $list, $selected, $attributes, $optionsAttributes);
    }

    /**
     * @param string $name
     * @param string $dictionary
     * @param array $selectAttributes
     * @param array $optionAttributes
     * @param string $optionContent
     * @return \Illuminate\Support\HtmlString
     */
    public function vueSelect($name, $dictionary, $selectAttributes = [], array $optionAttributes = [], $optionContent = '{{ name }}')
    {
        $selectAttributes = $this->prepareVueOptions($name, $selectAttributes, 'select');

        $selectAttributes['id'] = $this->getIdAttribute($name, $selectAttributes);
        if (!isset($selectAttributes['name'])) {
            $selectAttributes['name'] = $name;
        }
        $selectAttributes['class'] = isset($selectAttributes['class']) ?
                self::FORM_CONTROL . ' ' . $selectAttributes['class'] :
                self::FORM_CONTROL;

        $selectedValue = isset($selectAttributes['value']) ? $selectAttributes['value'] : null;
        unset($selectAttributes['value']);

        $html = [];
        if (isset($selectAttributes['placeholder'])) {
            $html[] = $this->placeholderOption($selectAttributes['placeholder'], $selectedValue);
            unset($selectAttributes['placeholder']);
        }

        if (!isset($optionAttributes['v-for'])) {
            $vForParams = version_compare(ServiceProvider::$vueVersion, 2, '>=') ? 'name, id' : 'id, name';
            $optionAttributes['v-for'] = "($vForParams) in $dictionary";
        }
        if (!isset($optionAttributes['v-bind:value']) && !isset($optionAttributes[':value'])) {
            $optionAttributes['v-bind:value'] = 'id';
        }
        $html[] = $this->toHtmlString('<option' . $this->html->attributes($optionAttributes) . ">$optionContent</option>");

        return $this->toHtmlString(implode('', [
                '<select' . $this->html->attributes($selectAttributes) . '>',
                implode('', $html),
                '</select>',
        ]));
    }

    /**
     * @inheritdoc
     */
    public function textarea($name, $value = null, $attributes = [])
    {
        $attributes = $this->prepareVueOptions($name, $attributes, __FUNCTION__);
        return parent::textarea($name, $value, $attributes);
    }

    /**
     * @param string $name
     * @param array $options
     * @param string $type
     * @return array
     */
    public function prepareVueOptions($name, array $options = [], $type = 'text')
    {
        if ($this->getRequest()) {
            if ($rules = $this->getRulesParser()->generateVueRules($name)) {
                $options['data-rules'] = implode('|', array_filter(array_merge([
                        isset($options['data-rules']) ? $options['data-rules'] : null,
                ], $rules)));
                $this->addParsedRules($rules);

                $options['v-validation-messages'] = $this->html->encodeJs(array_merge(
                        isset($options['v-validation-messages']) ? $options['v-validation-messages'] : [],
                        $this->generateVueMessages($rules)
                ));
            }

            if (isset($options['data-rules']) && !isset($options['data-as'])) {
                $options['data-as'] = $this->getRulesParser()->getAttributeDisplayName($name);
            }

            if (isset($options['data-rules']) && !isset($options['v-validation-error'])) {
                $attributeName = $this->getRulesParser()->convertInputNameToAttribute($name);
                $error = ($this->getRequest()->getSession()->get('errors') ?: new ViewErrorBag)->first($attributeName);
                if ($error) {
                    $options['v-validation-error'] = $this->html->encodeJs($error);
                }
            }
        }

        if (isset($options['data-rules']) && !isset($options['v-validate'])) {
            $options['v-validate'] = true;
        }

        if (isset($options['data-rules']) && !isset($options['data-scope'])) {
            $currentVueScope = $this->getCurrentVueScope();
            if ($this->getFormId() !== $currentVueScope) {
                $options['data-scope'] = $currentVueScope;
            }
        }

        return $options;
    }

    /**
     * @param Contracts\RuleConverter[] $rules
     * @return array
     */
    protected function generateVueMessages(array $rules)
    {
        $result = [];
        foreach ($rules as $rule) {
            $message = $rule->getMessage();
            foreach ($rule->getVueRules() as $vueRule => $vueParams) {
                $vueParams = array_values(array_map('strval', $vueParams));
                $result[] = [$vueRule, $vueParams, $message];
            }
        }
        return $result;
    }

    /**
     * @param string $name input name
     * @param array $attributes
     * @return \Illuminate\Support\HtmlString
     */
    public function vueError($name, array $attributes = [])
    {
        $jsEncodedName = $this->html->encodeJs($name);
        $jsScope = $this->html->encodeJs($this->getCurrentVueScope());
        $label = "{{ errors.first($jsEncodedName, $jsScope) }}";

        if (!array_key_exists('v-show', $attributes)) {
            $attributes['v-show'] = "errors.has($jsEncodedName, $jsScope)";
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
     * @param boolean|null $addVueClass
     * @return \Bootstrapper\ControlGroup
     * @throws \Bootstrapper\Exceptions\ControlGroupException
     */
    public function controlGroup($inputName, $label, $control, $help = null, $labelSize = null, $controlSize = null, $addVueClass = null)
    {
        if ($this->getRequest() && $help === null) {
            $help = $this->vueError($inputName);
        }

        $result = ControlGroup::generate(
                $label,
                $control,
                $help,
                $labelSize,
                $controlSize
        );
        /* @var $result Solbeg\VueValidation\\Bootstrapper\ControlGroup */

        if (($addVueClass === null && $this->getRequest()) || $addVueClass) {
            $jsErrorClass = $this->html->encodeJs(self::$controlGroupErrorClass);
            $jsInputName = $this->html->encodeJs($inputName);
            $jsScope = $this->html->encodeJs($this->getCurrentVueScope());
            $result->withAttributes([
                    'v-bind:class' => "{{$jsErrorClass}: errors.has($jsInputName, $jsScope)}",
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
            if (is_string($request)) {
                $requestClass = $request;
            } else {
                $requestClass = is_object($request)
                        ? get_class($request)
                        : gettype($request);
            }
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
     * @param string|null $scope
     * @return static $this
     */
    public function beginVueScope($scope)
    {
        $this->vueScopes[] = $scope;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrentVueScope()
    {
        return count($this->vueScopes) ? end($this->vueScopes) : null;
    }

    /**
     * @param bool|false|null|string $checkScope
     * @return static $this
     * @throws \LogicException
     */
    public function endVueScope($checkScope = false)
    {
        $currentScope = array_pop($this->vueScopes);
        if ($checkScope !== false && $checkScope !== $currentScope) {
            $checkScope = $checkScope === null ? 'NULL' : $checkScope;
            $currentScope = $currentScope === null ? 'NULL' : $currentScope;
            throw new \LogicException("Cannot end the '$checkScope' Vue scope, because current scope is '$currentScope'.");
        }
        return $this;
    }

    /**
     * @return static $this
     */
    public function clearVueScopes()
    {
        $this->vueScopes = [];
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
            /**
             * In the laravel 5.4 'make' method no longer accepts a second array of parameters. The 'makeWith' method
            allows functionality similar to old "make" functionality
             */
            $this->rulesParser = $this->getContainer()->makeWith(RulesParser::class, [
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

    /**
     * Returns empty string so you can easily use it in template, e.g.:
     *
     * ```
     *  {{ F::setRequest($request)->beginVueScope('some-scope') }}
     * ```
     *
     * @return string
     */
    public function __toString()
    {
        return '';
    }
}
