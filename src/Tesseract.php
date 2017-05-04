<?php

namespace BiffBangPow\TesseractPHP;

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

    public function authenticateUser(string $userID, string $password, string $dataSource)
    {
        $success = false;

        $result = $this->soapClient->__soapCall("AuthenticateUser", [
            'sUID' => $userID,
            'sPWD' => $password,
            'sDataSource' => $dataSource,
            'bSuccess' => &$success
        ]);

        if ($success) {
            $this->securityToken = $result;
        } else {
            throw new UserAuthenticationException($result);
        }
    }
}
