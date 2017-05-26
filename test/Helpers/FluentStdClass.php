<?php

namespace BiffBangPow\TesseractPHP\Test\Helpers;

class FluentStdClass extends \stdClass
{
    /**
     * @param mixed $property
     * @param mixed $value
     * @return $this
     */
    public function set($property, $value)
    {
        $this->{$property} = $value;
        return $this;
    }

    /**
     * @return FluentStdClass
     */
    public static function create()
    {
        return new self();
    }
}
