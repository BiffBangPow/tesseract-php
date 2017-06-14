<?php

namespace BiffBangPow\TesseractPHP\Test;

use \Mockery as m;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    protected function tearDown()
    {
        m::close();
        parent::tearDown();
    }
}
