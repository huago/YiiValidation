<?php
/**
 * Created by PhpStorm.
 * User: soooldier
 * Date: 3/19/15
 * Time: 10:36
 */
namespace validation\validators;

/**
 * NumberValidator validates that the attribute value is a number.
 *
 * The format of the number must match the regular expression specified in [[integerPattern]] or [[numberPattern]].
 * Optionally, you may configure the [[max]] and [[min]] properties to ensure the number
 * is within certain range.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class NumberValidator extends Validator
{
    /**
     * @var boolean whether the attribute value can only be an integer. Defaults to false.
     */
    public $integerOnly = false;
    /**
     * @var integer|float upper limit of the number. Defaults to null, meaning no upper limit.
     */
    public $max;
    /**
     * @var integer|float lower limit of the number. Defaults to null, meaning no lower limit.
     */
    public $min;
    /**
     * @var string user-defined error message used when the value is bigger than [[max]].
     */
    public $tooBig;
    /**
     * @var string user-defined error message used when the value is smaller than [[min]].
     */
    public $tooSmall;
    /**
     * @var string the regular expression for matching integers.
     */
    public $integerPattern = '/^\s*[+-]?\d+\s*$/';
    /**
     * @var string the regular expression for matching numbers. It defaults to a pattern
     * that matches floating numbers with optional exponential part (e.g. -1.23e-10).
     */
    public $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = $this->integerOnly ? '{attribute} must be an integer.' : '{attribute} must be a number.';
        }
        if ($this->min !== null && $this->tooSmall === null) {
            $this->tooSmall = '{attribute} must be no less than {min}.';
        }
        if ($this->max !== null && $this->tooBig === null) {
            $this->tooBig = '{attribute} must be no greater than {max}.';
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (is_array($value)) {
            return array('{attribute} is invalid.', array());
        }
        $pattern = $this->integerOnly ? $this->integerPattern : $this->numberPattern;
        if (!preg_match($pattern, "$value")) {
            return array($this->message, array());
        } elseif ($this->min !== null && $value < $this->min) {
            return array($this->tooSmall, array('min' => $this->min));
        } elseif ($this->max !== null && $value > $this->max) {
            return array($this->tooBig, array('max' => $this->max));
        } else {
            return null;
        }
    }
}
