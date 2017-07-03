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

    <wsdl:message name="GetOrtFromKurzbzRequest">
        <wsdl:part minOccurs="1" maxOccurs="1" name="ort_kurzbz" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

     <wsdl:message name="GetOrtFromKurzbzResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="GetLehrveranstaltungFromId" type="tns:GetOrtFromKurzbz"/>
    </wsdl:message>

    <wsdl:message name="GetOrtFromKurzbz">
        <wsdl:part minOccurs="0" maxOccurs="1" name="bezeichnung" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="stockwerk" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="sitzplaetze" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="raumtyp" type="tns:ArrayOfRaumtyp"/>
    </wsdl:message>

    <s:complexType name="GetAuthentifizierung">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="1" name="username" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="passwort" type="s:string"/>
        </s:sequence>
    </s:complexType>

    <s:complexType name="ArrayOfRaumtyp">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="unbounded" name="RaumtypItem" type="tns:ArrayOfRaumtypItem"/>
        </s:sequence>
    </s:complexType>

    <s:complexType name="ArrayOfRaumtypItem">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="1" name="raumtyp_kurzbz" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="beschreibung" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="hierarchie" type="s:string"/>
        </s:sequence>
    </s:complexType>

	<wsdl:message name="GetRaeumeRequest">
		<wsdl:part minOccurs="0" maxOccurs="1" name="raumtyp_kurzbz" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

    <wsdl:message name="GetRaeumeResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="Raeume" type="tns:ArrayOfRaeume"/>
    </wsdl:message>

    <s:complexType name="ArrayOfRaeume">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="unbounded" name="RaumtypItem" type="tns:Raum"/>
        </s:sequence>
    </s:complexType>

     <wsdl:message name="Raum">
        <wsdl:part minOccurs="0" maxOccurs="1" name="ort_kurzbz" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="bezeichnung" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="planbezeichnung" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="sitzplaetze" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="aktiv" type="s:boolean"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="lehre" type="s:boolean"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="reservieren" type="s:boolean"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="stockwerk" type="s:string"/>
    </wsdl:message>

    <wsdl:message name="SearchRaumRequest">
        <wsdl:part minOccurs="1" maxOccurs="1" name="datum" type="s:string"/>
        <wsdl:part minOccurs="1" maxOccurs="1" name="von_zeit" type="s:string"/>
        <wsdl:part minOccurs="1" maxOccurs="1" name="bis_zeit" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="raumtyp" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="anzahl_personen" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="reservierung" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

    <wsdl:message name="SearchRaumResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="Raeume" type="tns:ArrayOfRaeume"/>
    </wsdl:message>

    <wsdl:portType name="ConfigPortType">
        <wsdl:operation name="getOrtFromKurzbz">
            <wsdl:input message="tns:GetOrtFromKurzbzRequest"/>
            <wsdl:output message="tns:GetOrtFromKurzbzResponse"/>
        </wsdl:operation>
        <wsdl:operation name="getRaeume">
            <wsdl:input message="tns:GetRaeumeRequest"/>
            <wsdl:output message="tns:GetRaeumeResponse"/>
        </wsdl:operation>
        <wsdl:operation name="searchRaum">
            <wsdl:input message="tns:SearchRaumRequest"/>
            <wsdl:output message="tns:SearchRaumResponse"/>
        </wsdl:operation>
    </wsdl:portType>

    <wsdl:binding name="ConfigBinding" type="tns:ConfigPortType">
        <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http" />
        <wsdl:operation name="getOrtFromKurzbz">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/getOrtFromKurzbz";?>"  />
            <wsdl:input>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
        <wsdl:operation name="getRaeume">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/getRaeume";?>"  />
            <wsdl:input>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
         <wsdl:operation name="searchRaum">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/searchRaum";?>"  />
            <wsdl:input>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
    </wsdl:binding>

    <wsdl:service name="Ort">
        <wsdl:port name="ConfigWebservicePort" binding="tns:ConfigBinding">
            <soap:address location="<?php echo APP_ROOT."soap/ort.soap.php?".microtime(true);?>"/>
        </wsdl:port>
    </wsdl:service>
</wsdl:definitions>
