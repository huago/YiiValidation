<?php
namespace validation;

require_once 'validators/Validator.php';

use validation\validators\Validator;

class Validation
{
    /**
     * @var array validation errors (attribute name => array of errors)
     */
    protected $errors;
    /**
     * 最近一次错误信息
     * @var
     */
    protected $last_error;
    /**
     * @var array
     */
    protected $validators = array();
    /**
     * @var array
     */
    public $data = array();

    /**
     * Set rules
     * @param array $rules
     * @return $this
     * @throws \Exception
     */
    public function setRules(array $rules)
    {
        $this->validators = array();
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                if ($rule instanceof Validator) {
                    $this->validators[] = $rules;
                } elseif (is_array($rule) && isset($rule[0], $rule[1])) { // attributes, validator type
                    $validator = Validator::createValidator($rule[1], (array) $rule[0], array_slice($rule, 2));
                    $this->validators[] = $validator;
                } else {
                    throw new \Exception('Invalid validation rule: a rule must specify both attribute names and validator type.');
                }
            }
        }
        return $this;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function execute(array $data)
    {
        $this->data = $data;
        if (!empty($this->validators)) {
            foreach ($this->validators as $validator) {
                $validator->validateAttributes($this);
            }
            return !$this->hasErrors();
        }
        return true;
    }

    /**
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Creates a new object using the given configuration.
     *
     * You may view this method as an enhanced version of the `new` operator.
     * The method supports creating an object based on a class name, a configuration array or
     * an anonymous function.
     * @param string|array|callable $type the object type. This can be specified in one of the following forms:
     *
     * - a string: representing the class name of the object to be created
     * - a configuration array: the array must contain a `class` element which is treated as the object class,
     *   and the rest of the name-value pairs will be used to initialize the corresponding object properties
     * - a PHP callable: either an anonymous function or an array representing a class method (`[$class or $object, $method]`).
     *   The callable should return a new instance of the object being created.
     *
     * @return object the created object
     * @throws \Exception
     */
    public static function createObject(array $type)
    {
        if (isset($type['class'])) {
            require_once (substr(str_replace('\\', DIRECTORY_SEPARATOR, $type['class']).".php", 11));
            $class = $type['class'];
            unset($type['class']);
            $object = new $class($type);
            return $object;
        } else {
            throw new \Exception('Object configuration must be an array containing a "class" element.');
        }
    }

    /**
     * Adds a new error to the specified attribute.
     * @param string $attribute attribute name
     * @param string $error new error message
     */
    public function addError($attribute, $error = '')
    {
        $this->last_error = $error;
        $this->errors[$attribute][] = $error;
    }

    /**
     * Returns a value indicating whether there is any validation error.
     * @param string|null $attribute attribute name. Use null to check all attributes.
     * @return boolean whether there is any error.
     */
    public function hasErrors($attribute = null)
    {
        return $attribute === null ? !empty($this->errors) : isset($this->errors[$attribute]);
    }

    /**
     * Returns the errors for all attribute or a single attribute.
     * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
     * @property array An array of errors for all attributes. Empty array is returned if no error.
     * The result is a two-dimensional array. See [[getErrors()]] for detailed description.
     * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
     * Note that when returning errors for all attributes, the result is a two-dimensional array, like the following:
     *
     * ~~~
     * [
     *     'username' => [
     *         'Username is required.',
     *         'Username must contain only word characters.',
     *     ],
     *     'email' => [
     *         'Email address is invalid.',
     *     ]
     * ]
     * ~~~
     *
     * @see getFirstErrors()
     * @see getFirstError()
     */
    public function getErrors($attribute = null)
    {
        if ($attribute === null) {
            return $this->errors === null ? array() : $this->errors;
        } else {
            return isset($this->errors[$attribute]) ? $this->errors[$attribute] : array();
        }
    }

    /**
     * 获取最新的一次错误信息
     * @return mixed
     */
    public function getLastError()
    {
        return $this->last_error;
    }

    //__set()方法用来设置私有属性
    public function __set($property_name, $value)
    {
    	$this->$property_name = $value;
    }
}
