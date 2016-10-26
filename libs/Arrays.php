<?php

class Arrays
{
    public static function getValueByIndex( $array, $i, $default = null )
    {
        if(!is_array($array) && !$array instanceof ArrayObject)
            return $default;

        $indexes = array_keys($array);
        if(self::existsValueAtIndex($indexes,$i))
            return $array[$indexes[$i]];

        return $default;
    }

    public static function getValueByIndexName( $array, $name, $default = null )
    {
        if(!is_array($array) && !$array instanceof ArrayObject)
            return $default;

        if(array_key_exists($name,$array))
            return $array[$name];

        return $default;
    }

    public static function existsValueAtIndex( $array, $i )
    {
        return array_key_exists($i,$array);
    }

    public static function existsValueAtIndexName( $array, $name )
    {
        return array_key_exists($name,$array);
    }
}
