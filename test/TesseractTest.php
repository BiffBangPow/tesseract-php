<?php

namespace Test\BiffBangPow\TesseractPHP;

use BiffBangPow\TesseractPHP\Exception\UserAuthenticationException;
use BiffBangPow\TesseractPHP\Tesseract;
use Mockery as m;

class TesseractTest extends \PHPUnit_Framework_TestCase
{

    const SECURITY_TOKEN = "t6y7u8i9o0p";

    public function testAuthenticateUser_CallsSoapClient()
    {
        $soapClient = $this->mockSoapClient(true);
        $tesseract = new Tesseract($soapClient);
        $userId = "john";
        $password = "password123";
        $dataSource = "data_source";

        $tesseract->authenticateUser($userId, $password, $dataSource);

        $soapClient->shouldHaveReceived("__soapCall")
            ->with("AuthenticateUser", $this->parameters([
                'sUID' => 'string',
                'sPWD' => 'string',
                'sDataSource' => 'string',
                'bSuccess' => 'boolean'
            ]))
            ->once();
    }

    public function testAuthenticateUser_InvalidParameters_ThrowsException()
    {
        $soapClient = $this->mockSoapClient(false);
        $tesseract = new Tesseract($soapClient);
        $userId = "john";
        $password = "password123";
        $dataSource = "data_source";
        $this->setExpectedException(UserAuthenticationException::class);

        $tesseract->authenticateUser($userId, $password, $dataSource);
    }

    /**
     * @param string $securityToken
     * @return m\MockInterface|\SoapClient
     */
    private function mockSoapClient(bool $authenticateUser = true)
    {

        $soapClient = m::mock(\SoapClient::class);
        $soapClient->shouldReceive('__soapCall')
            ->with(
                "AuthenticateUser",
                m::on(function($parameters) use ($authenticateUser) {
                    if (!is_array($parameters)) {
                        return false;
                    }
                    $parameters['bSuccess'] = $authenticateUser;
                    return true;
                })
            )
            ->andReturn(self::SECURITY_TOKEN)
        ;

        return $soapClient;
    }

    /**
     * @return m\Matcher\Closure
     */
    private function parameters($required)
    {
        return m::on(function ($parameters) use ($required) {
            if (!is_array($parameters)) {
                return false;
            }
            foreach ($required as $name => $type) {
                if(!array_key_exists($name, $parameters) || gettype($parameters[$name]) !== $type) {
                    return false;
                }
            }
            return true;
        });
    }
}
