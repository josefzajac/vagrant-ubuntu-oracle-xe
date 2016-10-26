<?php
namespace App\Model\Column;

class Column
{
    /* db base info */
    private $name;
    private $type;
    private $lenght;
    private $default;

    /* db index info */
    private $isnull;
    private $primary;
    private $unique;
    private $constraint;

    /* rendering & conditions */
    private $label;
    private $fieldType;
    private $grid		= false;
    private $gridLenght	= 100;

    private $isrequired;
    private $regexp;
    private $msg;
    private $panel;
    private $serialize = false;

    public function __construct($label,$name,$type='varchar',$default='',$lenght=255,$isnull=FALSE,$primary=FALSE,$unique=FALSE,$fieldType="varchar",$isrequired=FALSE,$regexp='',$msg=null,$serialize=null)
    {
        $this->label 		= $label;

        $this->name			= $name;
        $this->type			= $type;
        $this->lenght		= $lenght;
        $this->default		= $default;

        $this->isnull		= $isnull;
        $this->primary		= $primary;
        $this->unique		= $unique;

        $this->fieldType  	= $fieldType;

        $this->isrequired	= $isrequired;
        $this->regexp		= $regexp;
        $this->msg			= $msg;
        $this->serialize 	= $serialize;
    }

    public function grid($render=null, $gridLenght = 200)
    {

        if(!is_null($render))
        {
            $this->grid			= $render;
            $this->gridLenght	= $gridLenght;
        }

        return $this -> grid;
    }

    public function setGrid($render = null, $gridLenght = 200)
    {
        return $this->grid($render,$gridLenght);
    }

    public function setPanel( $panel )
    {
        $this -> panel = $panel;
    }

    public function setConstraint($array)
    {
        $this -> constraint = $array;
    }

    public function getConstraint()
    {
        return $this -> constraint;
    }

    public function getPanel()
    {
        return $this -> panel;
    }

    public function getGridLenght()
    {
        return $this -> gridLenght;
    }

    public function setFieldType($fieldType)
    {
        $this->fieldType = $fieldType;
    }

    public function getLabel()	{		return $this->label; 		}
    public function getName()	{		return $this->name;			}
    public function getType()	{		return $this->type;			}
    public function getLenght()	{		return $this->lenght;		}
    public function getIsNull()	{		return $this->isnull;		}
    public function getPrimary(){		return $this->primary;		}
    public function getUnique()	{		return $this->unique;		}
    public function getFieldType(){		return $this->fieldType;	}
    public function getIsRequired(){	return $this->isrequired;	}
    public function getRegexp()	{		return $this->regexp;		}
    public function getMsg()	{		return $this->msg;			}
    public function getDefault(){		return $this->default;		}
    public function getSerialize() {	return $this->serialize;	}
}
