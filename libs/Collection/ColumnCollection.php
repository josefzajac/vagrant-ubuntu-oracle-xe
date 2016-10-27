<?php

namespace Collections;

class ColumnCollection extends ArrayCollection
{
    /**
     * @param $key
     * @return ColumnCollection
     */
    public function filterAttr($key)
    {
        return $this->filter(function($x) use ($key) {
            return $x->getAttr($key);
        });
    }

    /**
     * @param $key
     * @return ColumnCollection
     */
    public function getAttr($key)
    {
        return $this->map(function($x) use ($key) {
            return $x->getAttr($key);
        });
    }
}
