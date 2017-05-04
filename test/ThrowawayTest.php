<?php

namespace Test\BiffBangPow\TesseractPHP;

use BiffBangPow\TesseractPHP\Exception\TesseractAPIException;
use BiffBangPow\TesseractPHP\Tesseract;

class ThrowawayTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateCall_Integartion()
    {
        $soap = new \SoapClient("https://tesseract.starcomcloud.co.uk/SC51/asmx/ServiceCentreAPI.asmx?wsdl");
        $tesseract = new Tesseract($soap);

        $tesseract->authenticateUser("biffbang", "pfpleisure", "TEST");
        var_dump($tesseract->createCall("<Call><Call_Status>OPEN</Call_Status><Call_CalT_Code>C1</Call_CalT_Code><Call_Site_Num>10005</Call_Site_Num></Call>"));
    }

    public function testRetrieveCall_Integartion()
    {
        $soap = new \SoapClient("https://tesseract.starcomcloud.co.uk/SC51/asmx/ServiceCentreAPI.asmx?wsdl", ['trace' => 1]);
        $tesseract = new Tesseract($soap);

        $tesseract->authenticateUser("biffbang", "pfpleisure", "TEST");
        $response = new \SimpleXMLElement($tesseract->retrieveCall(97076));
        var_dump($response);
    }

    public function testRetrieveCallNote_Integartion()
    {
        $soap = new \SoapClient("https://tesseract.starcomcloud.co.uk/SC51/asmx/ServiceCentreAPI.asmx?wsdl", ['trace' => 1]);
        $tesseract = new Tesseract($soap);

        $tesseract->authenticateUser("biffbang", "pfpleisure", "TEST");
        try {
            $tesseract->createCallNote("<CallNote>
    <CallNote_Call_Num>97076</CallNote_Call_Num>
    <CallNote_Datetime>2010-08-02T10:02:22</CallNote_Datetime>
    <CallNote_Memo>Customer requested a callback</CallNote_Memo>
    <CallNote_NoteAction_Code>1NU</CallNote_NoteAction_Code>
    <Call_Next_NoteAction_Code>1NU</Call_Next_NoteAction_Code>
    <CallNote_Employ_Num>5-DMIS</CallNote_Employ_Num>
</CallNote>");
        } catch (TesseractAPIException $ex) {
            var_dump($soap->__getLastRequest(), $soap->__getLastResponse());
            throw $ex;
        }
    }

    public function testSoapMethods()
    {
        $soap = new \SoapClient("https://tesseract.starcomcloud.co.uk/SC51/asmx/ServiceCentreAPI.asmx?wsdl", ['trace' => 1]);
        var_dump($soap->__getFunctions());
    }
}
