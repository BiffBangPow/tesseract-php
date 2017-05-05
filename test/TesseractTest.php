<?php

namespace Test\BiffBangPow\TesseractPHP;

use BiffBangPow\TesseractPHP\Exception\TesseractAPIException;
use BiffBangPow\TesseractPHP\Exception\UserAuthenticationException;
use BiffBangPow\TesseractPHP\Tesseract;
use Mockery as m;
use Test\BiffBangPow\TesseractPHP\FluentStdClass as s;

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
        $callXML = "<Call><Call_Status>OPEN</Call_Status><Call_CalT_Code>C1</Call_CalT_Code><Call_Site_Num>10005</Call_Site_Num></Call>";

        $soapClient = $this->mockSoapClient();
        $soapClient->shouldReceive("__soapCall")
            ->with("Create_Call", m::type('array'))
            ->andReturn(s::create()
                ->set('bSuccess', true)
                ->set('iNewCallNum', 5));

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
            ->with("Create_Call", m::type('array'))
            ->andReturn(s::create()
                ->set('bSuccess', true)
                ->set('iNewCallNum', $callNum))
        ;

        $tesseract = new Tesseract($soapClient);

        $result = $tesseract->createCall("<Call>xml...</Call>");

        $this->assertSame($callNum, $result);
    }

    public function testCreateCall_UnSuccessfulRequest_ThrowsException()
    {
        $errorMessage = "Error Message";

        $soapClient = $this->mockSoapClient();
        $soapClient->shouldReceive("__soapCall")
            ->with("Create_Call", m::type('array'))
            ->andReturn(s::create()
                ->set('bSuccess', false)
                ->set('Create_CallResult', $errorMessage))
        ;

        $tesseract = new Tesseract($soapClient);

        $this->setExpectedException(TesseractAPIException::class, $errorMessage);
        $tesseract->createCall("<Call>xml...</Call>");

    }

    public function testRetrieveCall_CallsSoapClient()
    {
        $callNum = 5;

        $soapClient = $this->mockSoapClient();
        $soapClient->shouldReceive("__soapCall")
            ->with("Retrieve_Call", m::type('array'))
            ->andReturn(s::create()
                ->set('bSuccess', true)
                ->set('Retrieve_CallResult', s::create()
                    ->set('any', "<Call>xml...</Call>")));

        $tesseract = new Tesseract($soapClient);

        $tesseract->authenticateUser("john", "password123", "data_source");
        $tesseract->retrieveCall($callNum);

        $soapClient->shouldHaveReceived("__soapCall")
            ->with("Retrieve_Call", $this->parametersWithRequired([
                'iCallNum'  => $callNum,
                'bGetExtendedData' => true
            ], [
                'bSuccess'
            ]))
            ->once();
    }

    public function testRetrieveCall_SuccessfulRequest_ReturnsCallXML()
    {
        $callXML = "<Call>xml...</Call>";

        $soapClient = $this->mockSoapClient();
        $soapClient->shouldReceive("__soapCall")
            ->with("Retrieve_Call", m::type('array'))
            ->andReturn(s::create()
                ->set('bSuccess', true)
                ->set('Retrieve_CallResult', s::create()
                    ->set('any', $callXML)));

        $tesseract = new Tesseract($soapClient);

        $result = $tesseract->retrieveCall(5);

        $this->assertSame($callXML, $result);
    }

    public function testRetrieveCall_UnSuccessfulRequest_ThrowsException()
    {
        $soapClient = $this->mockSoapClient();
        $soapClient->shouldReceive("__soapCall")
            ->with("Retrieve_Call", m::type('array'))
            ->andReturn(s::create()
                ->set('bSuccess', false)
                ->set('Retrieve_CallResult', s::create()
                    ->set('any', '<error xmlns="">Something went wrong...</error>')));

        $tesseract = new Tesseract($soapClient);

        $this->setExpectedException(TesseractAPIException::class);
        $tesseract->retrieveCall(5);
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
                m::type('array')
            )
            ->andReturn(s::create()
                ->set('bSuccess', $authenticateUser)
                ->set('AuthenticateUserResult', self::SECURITY_TOKEN));

        return $soapClient;
    }

    /**
     * @param array $requiredValues - an array of key value pairs where the key is the parameter that is required and the value is the value it is required to be
     * @param array $requiredReferences - an array of reference parameters that must be provided. These need to be included but we don't care what they are
     * @return m\Matcher\Closure
     */
    private function parametersWithRequired($requiredValues, $requiredReferences = [])
    {
        return m::on(function ($data) use ($requiredValues, $requiredReferences) {

            if (!is_array($data) || !is_array($data[0])) {
                return false;
            }

            //for some reason the parameters are always the first item of another array
            $parameters = $data[0];

            foreach ($requiredValues as $name => $value) {
                if(!array_key_exists($name, $parameters) || $parameters[$name] !== $value) {
                    return false;
                }
            }

            foreach ($requiredReferences as $name) {
                if(!array_key_exists($name, $parameters)) {
                    return false;
                }
            }
            return true;
        });
    }
}
