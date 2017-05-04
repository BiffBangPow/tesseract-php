# TesseractPHP

A library for interacting with the Tesseract API in PHP

## Installation

Add the repository to your composer.json...

    "repositories": [
      {
          "type": "vcs",
          "url": "git@github.com:BiffBangPow/tesseract-php.git"
      }
    ],

    composer require biffbangpow/tesseract-php


## Example usage

    $soapClient = new \SoapClient('<wsdl>');
    $tesseractPHP = new BiffBangPow\TesseractPHP\TesseractPHP($soapClient);
    $tesseractPHP->authenticateUser('<userId>', '<password>', '<dataSource>');
    
    $callNum = $tesseractPHP->createCall("<call-xml-goes-here>");

Symfony Dependency Injection example...

    tesseract_php.soap_client:
        class: SoapClient
        arguments:
            - '%tesseract_wsdl%'
            
    tesseract_php:
        class: BiffBangPow\TesseractPHP\TesseractPHP
        calls:
            - [authenticateUser, ['%tesseract_user_id', '%tesseract_password%', '%tesseract_data_source%']]
