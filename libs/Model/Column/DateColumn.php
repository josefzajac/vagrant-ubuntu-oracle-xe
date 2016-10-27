<?php
namespace App\Model\Column;

class DateColumn extends Column
{
    const DATE_FORMAT = 'd-m-y';

    public function __toString()
    {
        return (string) $this->value->format(self::DATE_FORMAT);
    }

    public function getValue()
    {
        return $this->value;
    }
}