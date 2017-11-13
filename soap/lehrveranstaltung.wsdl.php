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

    <wsdl:message name="GetLehrveranstaltungFromIdRequest">
        <wsdl:part minOccurs="0" maxOccurs="1" name="lehrveranstaltung_id" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="semester" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

        <wsdl:message name="GetLehrveranstaltungFromIdResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="GetLehrveranstaltungFromId" type="tns:GetLehrveranstaltungFromId"/>
    </wsdl:message>

    <s:complexType name="GetLehrveranstaltungFromId">
        <s:element minOccurs="0" maxOccurs="1" name="bezeichnung" type="s:string"/>
        <s:element minOccurs="0" maxOccurs="1" name="lehreverzeichnis" type="s:string"/>
        <s:element minOccurs="0" maxOccurs="1" name="lektoren" type="tns:ArrayOfLektorenItem"/>
        <s:element minOccurs="0" maxOccurs="1" name="gruppen" type="tns:ArrayOfGruppenItem"/>
    </s:complexType>

    <wsdl:message name="GetLehrveranstaltungFromStudiengangRequest">
        <wsdl:part minOccurs="0" maxOccurs="1" name="studiengang" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="semester" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="ausbildungssemester" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

    <wsdl:message name="GetLehrveranstaltungFromStudiengangResponse">
        <wsdl:part minOccurs="0" maxOccurs="unbounded" name="GetLehrveranstaltungFromStudiengang" type="tns:GetLehrveranstaltungFromStudiengang"/>
    </wsdl:message>

    <s:complexType name="GetLehrveranstaltungFromStudiengang">
        <s:element minOccurs="0" maxOccurs="1" name="bezeichnung" type="s:string"/>
        <s:element minOccurs="0" maxOccurs="1" name="lehreverzeichnis" type="s:string"/>
        <s:element minOccurs="0" maxOccurs="1" name="lektoren" type="tns:ArrayOfLektorenItem"/>
        <s:element minOccurs="0" maxOccurs="1" name="gruppen" type="tns:ArrayOfGruppenItem"/>
    </s:complexType>


    <s:complexType name="GetAuthentifizierung">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="1" name="username" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="passwort" type="s:string"/>
        </s:sequence>
    </s:complexType>

    <s:complexType name="ArrayOfLektorenItem">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="unbounded" name="LektorItem" nillable="true" type="tns:LektorItem"/>
        </s:sequence>
    </s:complexType>
    <s:complexType name="LektorItem">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="1" name="uid" type="s:string"/>
        </s:sequence>
    </s:complexType>
    <s:complexType name="ArrayOfGruppenItem">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="unbounded" name="GruppenItem" nillable="true" type="tns:GruppenItem"/>
        </s:sequence>
    </s:complexType>
    <s:complexType name="GruppenItem">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="1" name="studiengang" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="semester" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="verband" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="gruppe" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="gruppe_kurzbz" type="s:string"/>
        </s:sequence>
    </s:complexType>

    <wsdl:portType name="ConfigPortType">
        <wsdl:operation name="getLehrveranstaltungFromId">
            <wsdl:input message="tns:GetLehrveranstaltungFromIdRequest"/>
            <wsdl:output message="tns:GetLehrveranstaltungFromIdResponse"/>
        </wsdl:operation>
        <wsdl:operation name="getLehrveranstaltungFromStudiengang">
            <wsdl:input message="tns:GetLehrveranstaltungFromStudiengangRequest"/>
            <wsdl:output message="tns:GetLehrveranstaltungFromStudiengangResponse"/>
        </wsdl:operation>
    </wsdl:portType>

    <wsdl:binding name="ConfigBinding" type="tns:ConfigPortType">
        <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http" />
        <wsdl:operation name="getLehrveranstaltungFromId">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/getLehrveranstaltungFromId";?>"  />
            <wsdl:input>
               <soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
                <soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
        <wsdl:operation name="getLehrveranstaltungFromStudiengang">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/getLehrveranstaltungFromStudiengang";?>"/>
            <wsdl:input>
                <soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
               <soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
    </wsdl:binding>

    <wsdl:service name="Lehrveranstaltung">
        <wsdl:port name="ConfigWebservicePort" binding="tns:ConfigBinding">
            <soap:address location="<?php echo APP_ROOT."/soap/lehrveranstaltung.soap.php?".microtime(true);?>"/>
        </wsdl:port>
    </wsdl:service>
</wsdl:definitions>
