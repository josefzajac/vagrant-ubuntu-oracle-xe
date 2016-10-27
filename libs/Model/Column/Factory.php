<?php
namespace App\Model\Column;

class Factory
{
    /**
     * @param $type
     * @return Column|DateColumn
     */
    public static function create($type)
    {
        switch ($type)
        {
            case 'date' :
                return new DateColumn;
            case 'groupped':
                return new GrouppedColumn;

            default:
                return new Column;
        }
    }
}
