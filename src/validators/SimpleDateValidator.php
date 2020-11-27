<?php

namespace validation\validators;

use DateTime;

/**
 * 简易日期格式校验器
 * Class SimpleDateValidator
 * @package validation\validators
 */
class SimpleDateValidator extends Validator
{
    /**
     * @var 时区
     */
    public $timezone;

    /**
     * @var 时间戳格式
     */
    public $format = null;


    /**
     * @var 最早时间
     */
    public $min = null;

    /**
     * @var 最晚时间
     */
    public $max = null;

    /**
     * @var 早于最早时间信息提示
     */
    public $tooEarly = null;

    /**
     * @var 晚于最晚时间信息提示
     */
    public $tooLate = null;

    /**
     * @var 错误消息
     */
    public $message;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = empty($this->format) ? '{attribute} is invalid.' : '{attribute} match {format} failed';
        }
        if ($this->min !== null && $this->tooEarly === null) {
            $this->tooEarly = '{attribute} must be no less than {min}.';
        }
        if ($this->max !== null && $this->tooLate === null) {
            $this->tooLate = '{attribute} must be no greater than {max}.';
        }
    }

    private function _parseDate($value) {
        $timezone = new \DateTimeZone($this->timezone);
        if(is_int($value)) {
            // 时间戳形式
            $date = new DateTime();
            $date->setTimezone($timezone);
            $date->setTimestamp($value);

        } else if(is_string($value)) {
            // 字符串形式
            $date = DateTime::createFromFormat($this->format, $value, $timezone);
        }
        return $date;
    }

    /**
     * 带有时区的比较. 同时支持时间戳和日期两种格式
     * @param $value
     * @return array|null
     */
    private function _validate($value) {
        $date = $this->_parseDate($value);
        if ($this->min !== null) {
            $minDate = $this->_parseDate($this->min);
            $sign = $minDate->diff($date)->format('%R');
            if($sign == '-') {
                return array($this->tooEarly, array('min' => $this->min));
            }
        }
        if ($this->max !== null) {
            $maxDate = $this->_parseDate($this->max);
            $sign = $maxDate->diff($date)->format('%R');
            if($sign == '+') {
                return array($this->tooLate, array('max' => $this->max));
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function validateValue($value)
    {

        if (!$this->_validateDate($value)) {
            return array($this->message, array('format' => $this->format));
        }

        if(!empty($this->timezone)) {
            return $this->_validate($value);
        }

        $value = strtotime($value);
        if ($this->min !== null) {
            $min = is_int($this->min) ? $this->min : strtotime($this->min);
            if ($value < $min) {
                return array($this->tooEarly, array('min' => $this->min));
            }
        }

        if ($this->max !== null) {
            $max = is_int($this->max) ? $this->max : strtotime($this->mx);
            if ($value > $max) {
                return array($this->tooLate, array('max' => $this->max));
            }
        }
        return null;
    }

    private function _validateDate($date)
    {
        if (empty($this->format)) {
            return strtotime($date) !== false;
        }

        $timezone = is_null($this->timezone) ? null: new \DateTimeZone($this->timezone);
        $d = DateTime::createFromFormat($this->format, $date, $timezone);
        return $d && $d->format($this->format) == $date;
    }
}

/* vim: set ts=4 sw=4 sts=4 tw=120 et: */
