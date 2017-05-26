<?php

namespace BiffBangPow\TesseractPHP\Test;

use BiffBangPow\TesseractPHP\Exception\TesseractAPIException;
use BiffBangPow\TesseractPHP\Exception\UserAuthenticationException;
use BiffBangPow\TesseractPHP\TesseractPHP;
use \Mockery as m;
use BiffBangPow\TesseractPHP\Test\Helpers\FluentStdClass as std;

class TesseractPHPTest extends BaseTestCase
{
    const DATA_SOURCE = 'data_source';
    const PASSWORD = 'password123';
    const USER_ID = 'john';

    public function testAuthenticateUser_CallsSoapClient()
    {
        $soapClient = $this->mockSoapClient();
        $tesseractPHP = new TesseractPHP($soapClient);

        $tesseractPHP->authenticateUser(self::USER_ID, self::PASSWORD, self::DATA_SOURCE);

        $this->addToAssertionCount(1);
        $soapClient
            ->shouldHaveReceived("__soapCall")
            ->with(
                "AuthenticateUser",
                $this->parametersWithRequired(
                    [
                        'sUID'        => self::USER_ID,
                        'sPWD'        => self::PASSWORD,
                        'sDataSource' => self::DATA_SOURCE,
                    ],
                    [
                        'bSuccess',
                    ]
                )
            )
            ->once()
        ;
    }

    public function testAuthenticateUser_UnSuccessfulRequest_ThrowsException()
    {
        $soapClient = $this->mockSoapClient(false);
        $tesseractPHP = new TesseractPHP($soapClient);

        $this->expectException(UserAuthenticationException::class);

        $tesseractPHP->authenticateUser(self::USER_ID, self::PASSWORD, self::DATA_SOURCE);
    }

    public function testCreateCall_CallsSoapClient()
    {
        $callXML = '<Call>' .
            '<Call_Status>OPEN</Call_Status>' .
            '<Call_CalT_Code>C1</Call_CalT_Code>' .
            '<Call_Site_Num>10005</Call_Site_Num>' .
            '</Call>';
        $soapClient = $this->mockSoapClient();
        $soapClient->shouldReceive("__soapCall")
            ->with("Create_Call", m::type('array'))
            ->andReturn(std::create()
                ->set('bSuccess', true)
                ->set('iNewCallNum', 5))
        ;
        $tesseractPHP = $this->createAuthenticatedTesseractPHP($soapClient);

        $tesseractPHP->createCall($callXML);

        $this->addToAssertionCount(1);
        $soapClient->shouldHaveReceived("__soapCall")
            ->with("Create_Call", $this->parametersWithRequired([
                'sDataIn'  => $callXML,
                'sTokenID' => self::SECURITY_TOKEN,
            ], [
                'iNewCallNum',
                'bSuccess',
            ]))
            ->once()
        ;
    }

    public function testCreateCall_SuccessfulRequest_ReturnsNewCallNum()
    {
        $callNum = 5;
        $soapClient = $this->mockSoapClient();
        $soapClient
            ->shouldReceive("__soapCall")
            ->with("Create_Call", m::type('array'))
            ->andReturn(
                std::create()
                    ->set('bSuccess', true)
                    ->set('iNewCallNum', $callNum)
            )
        ;
        $tesseractPHP = $this->createAuthenticatedTesseractPHP($soapClient);

        $result = $tesseractPHP->createCall("<Call>xml...</Call>");

        $this->assertSame($callNum, $result);
    }

    public function testCreateCall_UnSuccessfulRequest_ThrowsException()
    {
        $errorMessage = "Error Message";
        $soapClient = $this->mockSoapClient();
        $soapClient
            ->shouldReceive("__soapCall")
            ->with("Create_Call", m::type('array'))
            ->andReturn(std::create()
                ->set('bSuccess', false)
                ->set('Create_CallResult', $errorMessage))
        ;

        $tesseractPHP = $this->createAuthenticatedTesseractPHP($soapClient);

        $this->expectException(TesseractAPIException::class);
        $this->expectExceptionMessage($errorMessage);
        $tesseractPHP->createCall("<Call>xml...</Call>");
    }

    public function testRetrieveCall_CallsSoapClient()
    {
        $callNum = 5;
        $getExtendedData = false;

        $soapClient = $this->mockSoapClient();
        $soapClient->shouldReceive("__soapCall")
            ->with("Retrieve_Call", m::type('array'))
            ->andReturn(std::create()
                ->set('bSuccess', true)
                ->set('Retrieve_CallResult', std::create()
                    ->set('any', "<Call>xml...</Call>")))
        ;

        $tesseractPHP = $this->createAuthenticatedTesseractPHP($soapClient);

        $tesseractPHP->retrieveCall($callNum, $getExtendedData);

        $this->addToAssertionCount(1);
        $soapClient->shouldHaveReceived("__soapCall")
            ->with("Retrieve_Call", $this->parametersWithRequired([
                'iCallNum'         => $callNum,
                'bGetExtendedData' => $getExtendedData,
            ], [
                'bSuccess',
            ]))
            ->once()
        ;
    }

    public function testRetrieveCall_SuccessfulRequest_ReturnsCallXML()
    {
        $callXML = "<Call>xml...</Call>";

        $soapClient = $this->mockSoapClient();
        $soapClient->shouldReceive("__soapCall")
            ->with("Retrieve_Call", m::type('array'))
            ->andReturn(std::create()
                ->set('bSuccess', true)
                ->set('Retrieve_CallResult', std::create()
                    ->set('any', $callXML)))
        ;

        $tesseractPHP = $this->createAuthenticatedTesseractPHP($soapClient);

        $result = $tesseractPHP->retrieveCall(5);

        $this->assertSame($callXML, $result);
    }

    public function testRetrieveCall_UnSuccessfulRequest_ThrowsException()
    {
        $soapClient = $this->mockSoapClient();
        $soapClient->shouldReceive("__soapCall")
            ->with("Retrieve_Call", m::type('array'))
            ->andReturn(std::create()
                ->set('bSuccess', false)
                ->set('Retrieve_CallResult', std::create()
                    ->set('any', '<error xmlns="">Something went wrong...</error>')))
        ;

        $tesseractPHP = $this->createAuthenticatedTesseractPHP($soapClient);

        $this->expectException(TesseractAPIException::class);
        $tesseractPHP->retrieveCall(5);
    }

    /**
     * @param array $requiredValues Array of key value pairs where the key is the parameter that is required
     *                              and the value is the value it is required to be.
     * @param array $requiredReferences Array of reference parameters that must be provided. These need to be
     *                              included but we don't care what they are.
     * @return m\Matcher\Closure
     */
    private function parametersWithRequired(array $requiredValues, array $requiredReferences = [])
    {
        return m::on(function ($data) use ($requiredValues, $requiredReferences) {

            if (!is_array($data) || !is_array($data[0])) {
                return false;
            }

            // For some reason the parameters are always the first item of another array
            $parameters = $data[0];

            foreach ($requiredValues as $name => $value) {
                if (!array_key_exists($name, $parameters) || $parameters[$name] !== $value) {
                    return false;
                }
            }

            foreach ($requiredReferences as $name) {
                if (!array_key_exists($name, $parameters)) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * @param \SoapClient $soapClient
     * @return TesseractPHP
     */
    private function createAuthenticatedTesseractPHP(\SoapClient $soapClient): TesseractPHP
    {
        $tesseractPHP = new TesseractPHP($soapClient);
        $tesseractPHP->authenticateUser(self::USER_ID, self::PASSWORD, self::DATA_SOURCE);
        return $tesseractPHP;
    }
}
