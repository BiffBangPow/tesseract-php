<?php

namespace BiffBangPow\TesseractPHP\Test;

use \Mockery as m;
use BiffBangPow\TesseractPHP\Test\Helpers\FluentStdClass as std;

abstract class BaseTestCase extends \PHPUnit\Framework\TestCase
{
    const SECURITY_TOKEN = "t6y7u8i9o0p";

    /**
     * @param bool $authenticateUser
     * @return \SoapClient|m\Mock
     */
    protected function mockSoapClient(bool $authenticateUser = true)
    {
        /** @var \SoapClient|m\Mock $soapClient */
        $soapClient = m::mock(\SoapClient::class);
        $soapClient->shouldReceive('__soapCall')
            ->with(
                "AuthenticateUser",
                m::type('array')
            )
            ->andReturn(std::create()
                ->set('bSuccess', $authenticateUser)
                ->set('AuthenticateUserResult', self::SECURITY_TOKEN))
        ;

        return $soapClient;
    }

    protected function tearDown()
    {
        m::close();
        parent::tearDown();
    }
}
