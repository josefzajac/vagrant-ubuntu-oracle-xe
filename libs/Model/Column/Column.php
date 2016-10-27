<?php
namespace App\Model\Column;

class Column
{
    /* db base info */
    private $name;
    private $type;
    private $default;
    protected $value;

    /* db index info */
    private $constraint;

    /* rendering & conditions */
    private $label;
    private $fieldType;
    private $grid = false;
    private $gridLenght = 100;

    private $isrequired;
    private $regexp;
    private $msg;
    private $panel;
    private $serialize = false;
    protected $extra;

    public function setValues(
        $label,
        $name,
        $type = 'varchar',
        $default = '',
        $fieldType = "varchar",
        $isrequired = false,
        $regexp = '',
        $msg = null,
        $serialize = null
    ) {
        $this->label = $label;

        $this->name = $name;
        $this->type = $type;
        $this->default = $default;

        $this->fieldType = $fieldType;

        $this->isrequired = $isrequired;
        $this->regexp = $regexp;
        $this->msg = $msg;
        $this->serialize = $serialize;

        return $this;
    }

    public function grid($render = null, $gridLenght = 200)
    {

        if (!is_null($render)) {
            $this->grid = $render;
            $this->gridLenght = $gridLenght;
        }

        return $this->grid;
    }

    public function setGrid($render = null, $gridLenght = 200)
    {
        return $this->grid($render, $gridLenght);
    }

    public function setPanel($panel)
    {
        $this->panel = $panel;
    }

    public function setConstraint($array)
    {
        $this->constraint = $array;
    }

    public function getConstraint()
    {
        return $this->constraint;
    }

    public function getPanel()
    {
        return $this->panel;
    }

    public function getGridLenght()
    {
        return $this->gridLenght;
    }

    public function setFieldType($fieldType)
    {
        $this->fieldType = $fieldType;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getPrimary()
    {
        return $this->primary;
    }

    public function getUnique()
    {
        return $this->unique;
    }

    public function getFieldType()
    {
        return $this->fieldType;
    }

    public function getIsRequired()
    {
        return $this->isrequired;
    }

    public function getRegexp()
    {
        return $this->regexp;
    }

    public function getMsg()
    {
        return $this->msg;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function getSerialize()
    {
        return $this->serialize;
    }

    public function getAttr($key)
    {
        if (! isset($this->{$key}))
            throw new \Exception('No valid KEY in App\Model\Column');

        return $this->{$key};
    }

    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    public function setValue($val)
    {
        $this->value = $val;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return (string) $this->value;
    }
}
