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
        $success = false;

        $result = $this->soapClient->__soapCall("AuthenticateUser", [
            'sUID' => $userID,
            'sPWD' => $password,
            'sDataSource' => $dataSource,
            'bSuccess' => &$success
        ]);

        if (!$success) {
            throw new UserAuthenticationException($result);
        }

        $this->securityToken = $result;
    }

    /**
     * @param string $callXML
     * @return int - New Call Number
     */
    public function createCall(string $callXML): int
    {
        $success = false;
        $callNum = 0;

        $result = $this->soapClient->__soapCall("Create_Call", [
            'sDataIn' => $callXML,
            'sTokenID' => $this->securityToken,
            'iNewCallNum' => &$callNum,
            'bSuccess' => &$success
        ]);

        if (!$success) {
            throw new TesseractAPIException($result);
        }

        return $callNum;
    }
}
