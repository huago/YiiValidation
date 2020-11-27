<?php
/**
 * Created by PhpStorm.
 * User: soooldier
 * Date: 3/30/15
 * Time: 20:54
 */

namespace validation\validators;

/**
 * StringValidator validates that the attribute value is of certain length.
 *
 * Note, this validator should only be used with string-typed attributes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class StringValidator extends Validator
{
    /**
     * @var integer|array specifies the length limit of the value to be validated.
     * This can be specified in one of the following forms:
     *
     * - an integer: the exact length that the value should be of;
     * - an array of one element: the minimum length that the value should be of. For example, `[8]`.
     *   This will overwrite [[min]].
     * - an array of two elements: the minimum and maximum lengths that the value should be of.
     *   For example, `[8, 128]`. This will overwrite both [[min]] and [[max]].
     */
    public $length;
    /**
     * @var integer maximum length. If not set, it means no maximum length limit.
     */
    public $max;
    /**
     * @var integer minimum length. If not set, it means no minimum length limit.
     */
    public $min;
    /**
     * @var string user-defined error message used when the value is not a string
     */
    public $message;
    /**
     * @var string user-defined error message used when the length of the value is smaller than [[min]].
     */
    public $tooShort;
    /**
     * @var string user-defined error message used when the length of the value is greater than [[max]].
     */
    public $tooLong;
    /**
     * @var string user-defined error message used when the length of the value is not equal to [[length]].
     */
    public $notEqual;
    /**
     * @var string the encoding of the string value to be validated (e.g. 'UTF-8').
     */
    public $encoding = 'UTF-8';

    /**
     * 是否允许转义字符带反斜杠. 如单引号"\'"
     * @var bool
     */
    public $enableSlash = true;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (is_array($this->length)) {
            if (isset($this->length[0])) {
                $this->min = $this->length[0];
            }
            if (isset($this->length[1])) {
                $this->max = $this->length[1];
            }
            $this->length = null;
        }
        if ($this->message === null) {
            $this->message = '{attribute} must be a string.';
        }
        if ($this->min !== null && $this->tooShort === null) {
            $this->tooShort = '{attribute} should contain at least {min} character(s).';
        }
        if ($this->max !== null && $this->tooLong === null) {
            $this->tooLong = '{attribute} should contain at most {max} character(s).';
        }
        if ($this->length !== null && $this->notEqual === null) {
            $this->notEqual = '{attribute} should contain {length} character(s).';
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (!is_string($value)) {
            return array($this->message, array());
        }
        if (!$this->enableSlash) {
            $value = stripslashes($value);
        }
        $length = mb_strlen($value, $this->encoding);

        if ($this->min !== null && $length < $this->min) {
            return array($this->tooShort, array('min' => $this->min));
        }
        if ($this->max !== null && $length > $this->max) {
            return array($this->tooLong, array('max' => $this->max));
        }
        if ($this->length !== null && $length !== $this->length) {
            return array($this->notEqual, array('length' => $this->length));
        }
        return null;
    }
}
