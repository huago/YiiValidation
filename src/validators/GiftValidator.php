<?php
/**
 * Created by PhpStorm.
 * User: soooldier
 * Date: 3/30/15
 * Time: 20:54
 */

namespace validation\validators;

use Jaguar\Jaguar;
use Rider\Tools\Gift;

/**
 * GiftValidator validates that the attribute value is a valid gift key whether or not
 *
 * @author chentiebing <chentiebing@didiglobal.com>
 * @since 2.0
 */
class GiftValidator extends Validator
{
    /**
     * @var integer maximum length. If not set, it means no maximum length limit.
     */
    public $max;

    /**
     * @var string user-defined error message used when the length of the value is greater than [[max]].
     */
    public $tooLong;

    public $namespace;

    private $gift;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->max !== null && $this->tooLong === null) {
            $this->tooLong = '{attribute} should contain at most {max} character(s).';
        }

        if($this->namespace == null) {
            $this->namespace = Jaguar::isOnlineMode() ? (Gift::PRIVATE_NAMESPACE) : 'anything';
        }

        if ($this->message === null) {
            $this->message = '{attribute} must be a valid gift resource key.';
        }
        $this->gift = new Gift();
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {

        if ($this->max !== null && strlen($value) > $this->max) {
            return array($this->tooLong, array('max' => $this->max));
        }

        if(!$this->gift->fetch($this->namespace, $value)) {
            return array($this->message, array());
        }

        return null;
    }
}

