<?php
/**
 * Created by PhpStorm.
 * User: soooldier
 * Date: 3/19/15
 * Time: 10:45
 */
namespace validation\validators;

/**
 * InlineValidator represents a validator which is defined as a method in the object being validated.
 *
 * The validation method must have the following signature:
 *
 * ~~~
 * function foo($attribute, $params)
 * ~~~
 *
 * where `$attribute` refers to the name of the attribute being validated, while `$params`
 * is an array representing the additional parameters supplied in the validation rule.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InlineValidator extends Validator
{
    /**
     * @var string|\Closure an anonymous function or the name of a model class method that will be
     * called to perform the actual validation. The signature of the method should be like the following:
     *
     * ~~~
     * function foo($attribute, $params)
     * ~~~
     */
    public $method;
    /**
     * @var array additional parameters that are passed to the validation method
     */
    public $params;

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $valid = (bool) call_user_func($this->method, $value, $this->params);
        return $valid ? null : array($this->message, array());
    }
}