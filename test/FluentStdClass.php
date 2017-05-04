<?php


namespace Test\BiffBangPow\TesseractPHP;


class FluentStdClass extends \stdClass
{

    /**
     * @param $property
     * @param $value
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