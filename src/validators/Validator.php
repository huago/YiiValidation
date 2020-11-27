<?php
/**
 * Created by PhpStorm.
 * User: soooldier
 * Date: 3/19/15
 * Time: 10:29
 */
namespace validation\validators;

use validation\Validation;

class Validator
{
    /**
     * @var array list of built-in validators (name => class or configuration)
     */
    public static $builtInValidators = array(
        'boolean' => 'validation\validators\BooleanValidator',
        'compare' => 'validation\validators\CompareValidator',
        'date' => 'validation\validators\DateValidator',
        'simpledate' => 'validation\validators\SimpleDateValidator',
        'default' => 'validation\validators\DefaultValueValidator',
        'double' => 'validation\validators\NumberValidator',
        'email' => 'validation\validators\EmailValidator',
        'exist' => 'validation\validators\ExistValidator',
        'file' => 'validation\validators\FileValidator',
        'filter' => 'validation\validators\FilterValidator',
        'image' => 'validation\validators\ImageValidator',
        'in' => 'validation\validators\RangeValidator',
        'match' => 'validation\validators\RegularExpressionValidator',
        'number' => 'validation\validators\NumberValidator',
        'required' => 'validation\validators\RequiredValidator',
        'safe' => 'validation\validators\SafeValidator',
        'string' => 'validation\validators\StringValidator',
        'unique' => 'validation\validators\UniqueValidator',
        'url' => 'validation\validators\UrlValidator',
        'gift' => 'validation\validators\GiftValidator',
    );
    /**
     * @var array|string attributes to be validated by this validator. For multiple attributes,
     * please specify them as an array; for single attribute, you may use either a string or an array.
     */
    public $attributes = array();
    /**
     * @var string the user-defined error message. It may contain the following placeholders which
     * will be replaced accordingly by the validator:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     *
     * Note that some validators may introduce other properties for error messages used when specific
     * validation conditions are not met. Please refer to individual class API documentation for details
     * about these properties. By convention, this property represents the primary error message
     * used when the most important validation condition is not met.
     */
    public $message;
    /**
     * @var boolean whether this validation rule should be skipped if the attribute being validated
     * already has some validation error according to some previous rules. Defaults to true.
     */
    public $skipOnError = true;
    /**
     * @var boolean whether this validation rule should be skipped if the attribute value
     * is null or an empty string.
     */
    public $skipOnEmpty = true;
    /**
     * @var callable a PHP callable that replaces the default implementation of [[isEmpty()]].
     * If not set, [[isEmpty()]] will be used to check if a value is empty. The signature
     * of the callable should be `function ($value)` which returns a boolean indicating
     * whether the value is empty.
     */
    public $isEmpty;
    /**
     * @var callable a PHP callable whose return value determines whether this validator should be applied.
     * The signature of the callable should be `function ($model, $attribute)`, where `$model` and `$attribute`
     * refer to the model and the attribute currently being validated. The callable should return a boolean value.
     *
     * This property is mainly provided to support conditional validation on the server side.
     * If this property is not set, this validator will be always applied on the server side.
     *
     * The following example will enable the validator only when the country currently selected is USA:
     *
     * ```php
     * function ($model) {
     *     return $model->country == Country::USA;
     * }
     * ```
     *
     * @see whenClient
     */
    public $when;

    /**
     * @ignore
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        if(!empty($params)) {
            foreach ($params as $key=>$val) {
                $this->$key = $val;
            }
        }
        $this->init();
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
    }

    /**
     * Creates a validator object.
     * @param mixed $type the validator type. This can be a built-in validator name,
     * a method name of the model class, an anonymous function, or a validator class name.
     * @param array|string $attributes list of attributes to be validated. This can be either an array of
     * the attribute names or a string of comma-separated attribute names.
     * @param array $params initial values to be applied to the validator properties
     * @return Validator the validator
     */
    public static function createValidator($type, $attributes, $params = array())
    {
        $params['attributes'] = $attributes;
        $isBuildIn = is_string($type) && substr($type, 0, 6) == 'build:';
        if ($type instanceof \Closure || $isBuildIn) {
            // method-based validator
            $params['class'] = __NAMESPACE__ . '\InlineValidator';
            $params['method'] = $isBuildIn ? substr($type, 6) : $type;
        } else {
            if (isset(static::$builtInValidators[$type])) {
                $type = static::$builtInValidators[$type];
            }
            if (is_array($type)) {
                $params = array_merge($type, $params);
            } else {
                $params['class'] = $type;
            }
        }

        return Validation::createObject($params);
    }


    /**
     * Validates the specified object.
     * @param  $model the data model being validated
     * Note that if an attribute is not associated with the validator,
     * it will be ignored.
     * If this parameter is null, every attribute listed in [[attributes]] will be validated.
     */
    public function validateAttributes($model)
    {
        $attributes = $this->attributes;
        foreach ($attributes as $attribute) {
            $skip = $this->skipOnError && $model->hasErrors($attribute)
                || $this->skipOnEmpty && $this->isEmpty(isset($model->data[$attribute]) ? $model->data[$attribute] : null);
            if (!$skip) {
                if ($this->when === null || call_user_func($this->when, $model->data, $attribute)) {
                    $this->validateAttribute($model, $attribute);
                }
            }
        }
    }

    /**
     * Validates a single attribute.
     * Child classes must implement this method to provide the actual validation logic.
     * @param $model the data model to be validated
     * @param string $attribute the name of the attribute to be validated.
     */
    public function validateAttribute($model, $attribute)
    {
        $result = $this->validateValue(isset($model->data[$attribute]) ? $model->data[$attribute] : null);
        if (!empty($result)) {
            $this->addError($model, $attribute, $result[0], $result[1]);
        }
    }

    /**
     * Adds an error about the specified attribute to the model object.
     * This is a helper method that performs message selection and internationalization.
     * @param $model the data model being validated
     * @param string $attribute the attribute being validated
     * @param string $message the error message
     * @param array $params values for the placeholders in the error message
     */
    public function addError($model, $attribute, $message, $params = array())
    {
        $value = isset($model->data[$attribute]) ? $model->data[$attribute] : null;
        $params['attribute'] = $attribute;
        $params['value'] = is_array($value) ? 'array()' : $value;
        $model->addError($attribute, str_replace(array_map(function($element){return '{'.$element.'}';}, array_keys($params)), array_values($params), $message));
    }

    /**
     * Checks if the given value is empty.
     * A value is considered empty if it is null, an empty array, or the trimmed result is an empty string.
     * Note that this method is different from PHP empty(). It will return false when the value is 0.
     * @param mixed $value the value to be checked
     * @return boolean whether the value is empty
     */
    public function isEmpty($value)
    {
        if ($this->isEmpty !== null) {
            return call_user_func($this->isEmpty, $value);
        } else {
            return $value === null || $value === array() || $value === '';
        }
    }
}