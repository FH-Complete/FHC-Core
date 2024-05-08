<?php
require_once('../config/vilesci.config.inc.php');
header("Content-type: text/xml");
echo "<?xml version='1.0' encoding='utf-8' ?>";
?>
<wsdl:definitions xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
	xmlns:mime="http://schemas.xmlsoap.org/wsdl/mime/"
	xmlns:tns="http://technikum-wien.at"
	xmlns:s="http://www.w3.org/2001/XMLSchema"
	xmlns:http="http://schemas.xmlsoap.org/wsdl/http/"
	targetNamespace="http://technikum-wien.at"
	xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/">

    <wsdl:message name="GetPersonFromUIDRequest">
        <wsdl:part minOccurs="1" maxOccurs="1" name="uid" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

     <wsdl:message name="GetPersonFromUIDResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="GetPersonFromUID" type="tns:Person"/>
    </wsdl:message>

    <wsdl:message name="SearchPersonRequest">
        <wsdl:part minOccurs="1" maxOccurs="1" name="searchItems" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

     <wsdl:message name="SearchPersonResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="Person" type="tns:ArrayOfPerson"/>
    </wsdl:message>

    <s:complexType name="GetAuthentifizierung">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="1" name="username" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="passwort" type="s:string"/>
        </s:sequence>
    </s:complexType>

    <wsdl:portType name="ConfigPortType">
        <wsdl:operation name="GetPersonFromUID">
            <wsdl:input message="tns:GetPersonFromUIDRequest"/>
            <wsdl:output message="tns:GetPersonFromUIDResponse"/>
        </wsdl:operation>
        <wsdl:operation name="SearchPerson">
            <wsdl:input message="tns:SearchPersonRequest"/>
            <wsdl:output message="tns:SearchPersonResponse"/>
        </wsdl:operation>
    </wsdl:portType>

    <wsdl:binding name="ConfigBinding" type="tns:ConfigPortType">
        <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http" />
        <wsdl:operation name="GetPersonFromUID">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/getPersonFromUID";?>"  />
            <wsdl:input>
                <soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
                <soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
        <wsdl:operation name="SearchPerson">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/searchPerson";?>"  />
            <wsdl:input>
                <soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
                <soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
    </wsdl:binding>

    <wsdl:service name="Person">
        <wsdl:port name="ConfigWebservicePort" binding="tns:ConfigBinding">
            <soap:address location="<?php echo APP_ROOT."soap/person.soap.php?".microtime(true);?>"/>
        </wsdl:port>
    </wsdl:service>
</wsdl:definitions>
