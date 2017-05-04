<?php

namespace BiffBangPow\TesseractPHP;

use BiffBangPow\TesseractPHP\Exception\TesseractAPIException;
use BiffBangPow\TesseractPHP\Exception\UserAuthenticationException;

class Tesseract
{

    /**
     * @var \SoapClient
     */
    private $soapClient;

    /**
     * @var string
     */
    private $securityToken;

    /**
     * Tesseract constructor.
     * @param \SoapClient $soapClient
     */
    public function __construct(\SoapClient $soapClient)
    {
        $this->soapClient = $soapClient;
    }

    /**
     * @param string $userID
     * @param string $password
     * @param string $dataSource
     * @throws UserAuthenticationException
     */
    public function authenticateUser(string $userID, string $password, string $dataSource)
    {
        $result = $this->soapClient->__soapCall("AuthenticateUser", [[
            'sUID' => $userID,
            'sPWD' => $password,
            'sDataSource' => $dataSource,
            'bSuccess' => true
        ]]);

        if (!$result->bSuccess) {
            throw new UserAuthenticationException($result->AuthenticateUserResult);
        }

        $this->securityToken = $result->AuthenticateUserResult;
    }

    /**
     * @param string $callXML
     * @return int - New Call Number
     */
    public function createCall(string $callXML): int
    {

        $result = $this->soapClient->__soapCall("Create_Call", [[
            'sDataIn' => $callXML,
            'sTokenID' => $this->securityToken,
            'iNewCallNum' => 0,
            'bSuccess' => false
        ]]);

        if (!$result->bSuccess) {
            throw new TesseractAPIException($result->Create_CallResult);
        }

        return intval($result->iNewCallNum);
    }

    /**
     * @param int $callNum
     * @param bool $getExtendedData - if true, will return extended data resolved from foreign keys
     * @return string - xml representation of call
     * @throws TesseractAPIException
     */
    public function retrieveCall(int $callNum, $getExtendedData = false): string
    {
        $result = $this->soapClient->__soapCall("Retrieve_Call", [[
            'iCallNum' => $callNum,
            'bGetExtendedData' => $getExtendedData,
            'sTokenID' => $this->securityToken,
            'bSuccess' => false
        ]]);

        if (!$result->bSuccess) {
            $resultXML = new \SimpleXMLElement($result->Retrieve_CallResult->any);
            throw new TesseractAPIException($resultXML[0]);
        }

        return $result->Retrieve_CallResult->any;
    }
}
