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
}
