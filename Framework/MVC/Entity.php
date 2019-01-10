<?php

namespace Framework\MVC;

class Entity
{
    public function __construct(array $array)
    {
        if (empty($array)) {
            return $this;
        }

        foreach ($array as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

    }
}