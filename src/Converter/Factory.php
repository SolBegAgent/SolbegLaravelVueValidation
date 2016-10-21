<?php

namespace Solbeg\VueValidation\Converter;

use Illuminate\Contracts\Container\Container;

use Solbeg\VueValidation\Contracts;

/**
 * Class Factory
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class Factory implements Contracts\ConverterFactory
{
    /**
     * @var array
     */
    protected $converters = [
        'Accepted' => AcceptedRule::class,
        'ActiveUrl' => UrlRule::class,
        'After' => CompareDateRule::class,
        'Alpha' => SimpleRule::class,
        'AlphaDash' => SimpleRule::class,
        'AlphaNum' => SimpleRule::class,
        'Before' => CompareDateRule::class,
        'Between' => BetweenRule::class,
        'Boolean' => BooleanRule::class,
        'Confirmed' => ConfirmedRule::class,
        'DateFormat' => DateFormatRule::class,
        'Digits' => SimpleRule::class,
        'DigitsBetween' => DigitsBetweenRule::class,
        'Dimensions' => DimensionsRule::class,
        'Email' => SimpleRule::class,
        'Filled' => RequiredRule::class,
        'Image' => SimpleRule::class,
        'In' => SimpleRule::class,
        'Ip' => SimpleRule::class,
        'Max' => MinMaxRule::class,
        'Mimetypes' => MimetypesRules::class,
        'Mimes' => MimetypesRules::class,
        'Min' => MinMaxRule::class,
        'NotIn' => SimpleRule::class,
        'Numeric' => NumericRule::class,
        'Regex' => RegexRule::class,
        'Required' => RequiredRule::class,
        'Same' => ConfirmedRule::class,
        'Timezone' => TimezoneRule::class,
        'Url' => UrlRule::class,
    ];

    /**
     * @var Container
     */
    private $container;

    /**
     * Factory constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function make($inputName, $rule, array $params = [], array $allRules = [], $attributeOptions = 0)
    {
        if (!isset($this->converters[$rule])) {
            throw new \InvalidArgumentException("Unknown rule: '$rule'.'");
        }
        $config = $this->converters[$rule];

        if ($config instanceof Contracts\RuleConverter) {
            return $config;
        } elseif ($config instanceof \Closure) {
            return $this->inline($config, $inputName, $rule, $params, $allRules, $attributeOptions);
        }

        $converter = $this->getContainer()->make($config, [
            'inputName' => $inputName,
            'rule' => $rule,
            'params' => $params,
            'allRules' => $allRules,
            'attributeOptions' => $attributeOptions,
        ]);
        if (!$converter instanceof Contracts\RuleConverter) {
            throw new \Exception("Bad config for '$rule' rule. It must implement \"" . Contracts\RuleConverter::class . '" interface.');
        }
        return $converter;
    }

    /**
     * @param callable $callback
     * @param string $inputName
     * @param string $rule
     * @param array $params
     * @param array $allRules
     * @param integer $attributeOptions
     * @return InlineRule
     */
    public function inline(callable $callback, $inputName, $rule, array $params = [], array $allRules = [], $attributeOptions = 0)
    {
        return new InlineRule($callback, $inputName, $rule, $params, $allRules, $attributeOptions);
    }

    /**
     * @inheritdoc
     */
    public function knows($rule)
    {
        return isset($this->converters[$rule]);
    }

    /**
     * @inheritdoc
     */
    public function extend($rule, $config)
    {
        $this->converters[$rule] = $config;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
