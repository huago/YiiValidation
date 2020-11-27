<?php
/**
 * Created by PhpStorm.
 * User: soooldier
 * Date: 3/19/15
 * Time: 19:27
 */
namespace validation\validators;


/**
 * RegularExpressionValidator validates that the attribute value matches the specified [[pattern]].
 *
 * If the [[not]] property is set true, the validator will ensure the attribute value do NOT match the [[pattern]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RegularExpressionValidator extends Validator
{
    /**
     * @var string the regular expression to be matched with
     */
    public $pattern;
    /**
     * @var boolean whether to invert the validation logic. Defaults to false. If set to true,
     * the regular expression defined via [[pattern]] should NOT match the attribute value.
     */
    public $not = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->pattern === null) {
            throw new \Exception('The "pattern" property must be set.');
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
        $valid = !is_array($value) &&
            (!$this->not && preg_match($this->pattern, $value)
                || $this->not && !preg_match($this->pattern, $value));

        return $valid ? null : array($this->message, array());
    }
}