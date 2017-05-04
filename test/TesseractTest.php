<?php

namespace Test\BiffBangPow\TesseractPHP;

use BiffBangPow\TesseractPHP\Exception\TesseractAPIException;
use BiffBangPow\TesseractPHP\Exception\UserAuthenticationException;
use BiffBangPow\TesseractPHP\Tesseract;
use Mockery as m;

class TesseractTest extends \PHPUnit_Framework_TestCase
{

    const SECURITY_TOKEN = "t6y7u8i9o0p";

    public function testAuthenticateUser_CallsSoapClient()
    {
        $soapClient = $this->mockSoapClient();
        $tesseract = new Tesseract($soapClient);
        $userId = "john";
        $password = "password123";
        $dataSource = "data_source";

        $tesseract->authenticateUser($userId, $password, $dataSource);

        $soapClient->shouldHaveReceived("__soapCall")
            ->with("AuthenticateUser", $this->parametersWithRequired([
                'sUID'        => $userId,
                'sPWD'        => $password,
                'sDataSource' => $dataSource
            ], [
                'bSuccess'
            ]))
            ->once()
        ;
    }

    public function testAuthenticateUser_UnSuccessfulRequest_ThrowsException()
    {
        $soapClient = $this->mockSoapClient(false);
        $tesseract = new Tesseract($soapClient);
        $userId = "john";
        $password = "password123";
        $dataSource = "data_source";
        $this->setExpectedException(UserAuthenticationException::class);

        $tesseract->authenticateUser($userId, $password, $dataSource);
    }

    public function testCreateCall_CallsSoapClient()
    {
        $callXML = "<Call>xml...</Call>";

        $soapClient = $this->mockSoapClient();
        $soapClient->shouldReceive("__soapCall")
            ->with("Create_Call", $this->parametersWithReferencesToBeReturned([
                'iNewCallNum' => 5,
                'bSuccess' => true
            ]))
        ;

        $tesseract = new Tesseract($soapClient);

        $tesseract->authenticateUser("john", "password123", "data_source");
        $tesseract->createCall($callXML);

        $soapClient->shouldHaveReceived("__soapCall")
            ->with("Create_Call", $this->parametersWithRequired([
                'sDataIn'  => $callXML,
                'sTokenID' => self::SECURITY_TOKEN
            ], [
                'iNewCallNum',
                'bSuccess'
            ]))
            ->once();
    }

    public function testCreateCall_SuccessfulRequest_ReturnsNewCallNum()
    {
        $callNum = 5;

        $soapClient = $this->mockSoapClient();
        $soapClient->shouldReceive("__soapCall")
            ->with("Create_Call", $this->parametersWithReferencesToBeReturned([
                'iNewCallNum' => $callNum,
                'bSuccess' => true
            ]))
        ;

        $tesseract = new Tesseract($soapClient);

        $tesseract->authenticateUser("john", "password123", "data_source");
        $result = $tesseract->createCall("<Call>xml...</Call>");

        $this->assertSame($callNum, $result);
    }

    public function testCreateCall_UnSuccessfulRequest_ThrowsException()
    {
        $errorMessage = "Error Message";

        $soapClient = $this->mockSoapClient();
        $soapClient->shouldReceive("__soapCall")
            ->with("Create_Call", $this->parametersWithReferencesToBeReturned([
                'bSuccess' => false
            ]))
            ->andReturn($errorMessage)
        ;

        $tesseract = new Tesseract($soapClient);

        $tesseract->authenticateUser("john", "password123", "data_source");

        $this->setExpectedException(TesseractAPIException::class, $errorMessage);
        $tesseract->createCall("<Call>xml...</Call>");

    }

    /**
     * @param bool $authenticateUser
     * @return m\MockInterface|\SoapClient
     */
    private function mockSoapClient(bool $authenticateUser = true)
    {
        $soapClient = m::mock(\SoapClient::class);
        $soapClient->shouldReceive('__soapCall')
            ->with(
                "AuthenticateUser",
                $this->parametersWithReferencesToBeReturned(['bSuccess' => $authenticateUser])
            )
            ->andReturn(self::SECURITY_TOKEN)
        ;

        return $soapClient;
    }

    /**
     * @param array $requiredValues - an array of key value pairs where the key is the parameter that is required and the value is the value it is required to be
     * @param array $requiredReferences - an array of reference parameters that must be provided so that values can be returned to them
     * @return m\Matcher\Closure
     */
    private function parametersWithRequired($requiredValues, $requiredReferences = [])
    {
        return m::on(function ($parameters) use ($requiredValues, $requiredReferences) {
            if (!is_array($parameters)) {
                throw new \Exception('$parameters is not an array');
                return false;
            }
            foreach ($requiredValues as $name => $value) {
                if(!array_key_exists($name, $parameters) || $parameters[$name] !== $value) {
                    throw new \Exception("Parameter '$name' with the value '$value' was not included");
                    return false;
                }
            }
            foreach ($requiredReferences as $name) {
                if(!array_key_exists($name, $parameters)) {
                    throw new \Exception("Parameter reference '$name' was not included");
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * @param array $referencesToSet - an array of key value pairs where the key is the reference to be set and the value is the value that will be returned to it
     * @return m\Matcher\Closure
     */
    private function parametersWithReferencesToBeReturned(array $referencesToSet)
    {
        return m::on(function ($parameters) use ($referencesToSet) {
            if (!is_array($parameters)) {
                return false;
            }
            foreach ($referencesToSet as $reference => $value) {
                $parameters[$reference] = $value;
            }
            return true;
        });
    }
}
