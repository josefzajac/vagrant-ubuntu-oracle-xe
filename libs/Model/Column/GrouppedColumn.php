<?php
namespace App\Model\Column;

class GrouppedColumn extends Column
{
    public function __toString()
    {
        return $this->getValue();
    }

    public function getValue()
    {
        $extra = $this->extra;
        return implode('<br/>', array_map(function($x) use ($extra) {
                $return = [];
                foreach (explode(',' ,$extra) as $e) {
                      $return[] = $x[$e];
                }

                return implode(' : ', $return);
            }, $this->value));
    }
}