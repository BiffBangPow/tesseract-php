# Tesseract PHP
A library for interacting with the Tesseract API in PHP

    $soapClient = new SoapClient("https://<server>/SC51/asmx/ServiceCentreAPI.asmx?wsdl");
    $tesseract = new Tesseract($soapClient);

    $tesseract->authenticateUser("ADMIN", "ADMIN", "LOCAL50_WIP");
    $callNum = $tesseract->createCall("<Call>my call xml...</Call>");