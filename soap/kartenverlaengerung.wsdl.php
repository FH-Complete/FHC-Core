<?php 
require_once('../config/cis.config.inc.php');
header("Content-type: text/plain");
echo "<?xml version='1.0' encoding='utf-8' ?>";
?>

<wsdl:definitions name="Kartenverlaengerung" 
targetNamespace="http://www.technikum-wien.at/soap/" 
xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" 
xmlns:tns="http://www.technikum-wien.at/soap/" 
xmlns:xsd="http://www.w3.org/2001/XMLSchema"    
xmlns:xsd1="http://localhost/soap/projekt.xsd" 
xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/">

    <wsdl:message name="getNumberRequest">
        <wsdl:part name="cardNr" type="xsd:string" minOccurs="1"></wsdl:part>
    </wsdl:message>

    <wsdl:message name="getNumberResponse">
        <wsdl:part name="datum" type="xsd:string"></wsdl:part>
        <wsdl:part name="errorMessage" type="xsd:string"></wsdl:part>
    </wsdl:message>

    <wsdl:portType name="ConfigPortType" >
        <wsdl:operation name="getNumber">
            <wsdl:input message="tns:getNumberRequest"></wsdl:input>
            <wsdl:output message="tns:getNumberResponse"></wsdl:output>        
        </wsdl:operation>
    </wsdl:portType>

    <wsdl:binding name="ConfigBinding" type="tns:ConfigPortType">
        <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http" />
        <wsdl:operation name="getNumber">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/getNumber";?>" />
            <wsdl:input> 
                <soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
                <soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
            </wsdl:output>
        </wsdl:operation> 
    </wsdl:binding>  

    <wsdl:service name="Kartenverlaengerung">
        <wsdl:port name="ConfigWebservicePort" binding="tns:ConfigBinding">
            <soap:address location="<?php echo APP_ROOT."soap/kartenverlaengerung.soap.php";?>"/>
        </wsdl:port>
    </wsdl:service>
</wsdl:definitions>
