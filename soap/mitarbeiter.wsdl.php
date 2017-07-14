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

    <wsdl:message name="GetMitarbeiterFromUIDRequest">
        <wsdl:part minOccurs="1" maxOccurs="1" name="uid" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

     <wsdl:message name="GetMitarbeiterFromUIDResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="GetMitarbeiterFromUID" type="tns:Mitarbeiter"/>
    </wsdl:message>

    <wsdl:message name="Mitarbeiter">
        <wsdl:part minOccurs="0" maxOccurs="1" name="vorname" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="nachname" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="titelpre" type="s:string"/>
		<wsdl:part minOccurs="0" maxOccurs="1" name="titelpost" type="s:string"/>
		<wsdl:part minOccurs="0" maxOccurs="1" name="uid" type="s:string"/>
		<wsdl:part minOccurs="0" maxOccurs="1" name="email" type="s:string"/>
    </wsdl:message>

    <s:complexType name="GetAuthentifizierung">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="1" name="username" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="passwort" type="s:string"/>
        </s:sequence>
    </s:complexType>

    <s:complexType name="ArrayOfMitarbeiter">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="unbounded" name="MitarbeiterItems" type="tns:MitarbeiterItem"/>
        </s:sequence>
    </s:complexType>

	<wsdl:message name="GetMitarbeiterRequest">
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

    <wsdl:message name="GetMitarbeiterResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="Mitarbeiter" type="tns:ArrayOfMitarbeiter"/>
    </wsdl:message>

    <s:complexType name="ArrayOfMitarbeiter">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="unbounded" name="MitarbeiterItem" type="tns:Mitarbeiter"/>
        </s:sequence>
    </s:complexType>

    <wsdl:message name="SearchMitarbeiterRequest">
    	<wsdl:part minOccurs="1" maxOccurs="1" name="filter" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

    <wsdl:message name="SearchMitarbeiterResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="Mitarbeiter" type="tns:ArrayOfMitarbeiter"/>
    </wsdl:message>

    <wsdl:portType name="ConfigPortType">
        <wsdl:operation name="GetMitarbeiterFromUID">
            <wsdl:input message="tns:GetMitarbeiterFromUIDRequest"/>
            <wsdl:output message="tns:GetMitarbeiterFromUIDResponse"/>
        </wsdl:operation>
        <wsdl:operation name="GetMitarbeiter">
            <wsdl:input message="tns:GetMitarbeiterRequest"/>
            <wsdl:output message="tns:GetMitarbeiterResponse"/>
        </wsdl:operation>
         <wsdl:operation name="SearchMitarbeiter">
            <wsdl:input message="tns:SearchMitarbeiterRequest"/>
            <wsdl:output message="tns:SearchMitarbeiterResponse"/>
        </wsdl:operation>
    </wsdl:portType>

    <wsdl:binding name="ConfigBinding" type="tns:ConfigPortType">
        <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http" />
        <wsdl:operation name="GetMitarbeiterFromUID">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/getMitarbeiterFromUID";?>"  />
            <wsdl:input>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
        <wsdl:operation name="GetMitarbeiter">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/getMitarbeiter";?>"  />
            <wsdl:input>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
          <wsdl:operation name="SearchMitarbeiter">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/SearchMitarbeiter";?>"  />
            <wsdl:input>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
    </wsdl:binding>

    <wsdl:service name="Mitarbeiter">
        <wsdl:port name="ConfigWebservicePort" binding="tns:ConfigBinding">
            <soap:address location="<?php echo APP_ROOT."soap/mitarbeiter.soap.php?".microtime(true);?>"/>
        </wsdl:port>
    </wsdl:service>
</wsdl:definitions>
