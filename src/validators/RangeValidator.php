<?php
/**
 * Created by PhpStorm.
 * User: soooldier
 * Date: 3/19/15
 * Time: 19:29
 */
namespace validation\validators;


/**
 * RangeValidator validates that the attribute value is among a list of values.
 *
 * The range can be specified via the [[range]] property.
 * If the [[not]] property is set true, the validator will ensure the attribute value
 * is NOT among the specified range.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RangeValidator extends Validator
{
    /**
     * @var array list of valid values that the attribute value should be among
     */
    public $range;
    /**
     * @var boolean whether the comparison is strict (both type and value must be the same)
     */
    public $strict = false;
    /**
     * @var boolean whether to invert the validation logic. Defaults to false. If set to true,
     * the attribute value should NOT be among the list of values defined via [[range]].
     */
    public $not = false;
    /**
     * @var boolean whether to allow array type attribute.
     */
    public $allowArray = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!is_array($this->range)) {
            throw new \Exception('The "range" property must be set.');
        }
        if ($this->message === null) {
            $this->message = '{attribute} is invalid.';
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (!$this->allowArray && is_array($value)) {
            return array($this->message, array());
        }

        $in = true;

        foreach ((is_array($value) ? $value : array($value)) as $v) {
            if (!in_array($v, $this->range, $this->strict)) {
                $in = false;
                break;
            }
        }

        return $this->not !== $in ? null : array($this->message, array());
    }
}