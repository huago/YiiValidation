<?php
/**
 * Created by PhpStorm.
 * User: soooldier
 * Date: 3/19/15
 * Time: 19:31
 */
namespace validation\validators;

/**
 * FilterValidator converts the attribute value according to a filter.
 *
 * FilterValidator is actually not a validator but a data processor.
 * It invokes the specified filter callback to process the attribute value
 * and save the processed value back to the attribute. The filter must be
 * a valid PHP callback with the following signature:
 *
 * ~~~
 * function foo($value) {...return $newValue; }
 * ~~~
 *
 * Many PHP functions qualify this signature (e.g. `trim()`).
 *
 * To specify the filter, set [[filter]] property to be the callback.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FilterValidator extends Validator
{
    /**
     * @var callable the filter. This can be a global function name, anonymous function, etc.
     * The function signature must be as follows,
     *
     * ~~~
     * function foo($value) {...return $newValue; }
     * ~~~
     */
    public $filter;
    /**
     * @var boolean whether the filter should be skipped if an array input is given.
     * If false and an array input is given, the filter will not be applied.
     */
    public $skipOnArray = false;
    /**
     * @var boolean this property is overwritten to be false so that this validator will
     * be applied when the value being validated is empty.
     */
    public $skipOnEmpty = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->filter === null) {
            throw new \Exception('The "filter" property must be set.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = isset($model->data[$attribute]) ? $model->data[$attribute] : null;
        if (!$this->skipOnArray || !is_array($value)) {
            $model->data[$attribute] = call_user_func($this->filter, $value);
        }
    }
}