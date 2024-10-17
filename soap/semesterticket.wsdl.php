<?php
require_once('../config/vilesci.config.inc.php');
header("Content-type: text/plain");
echo "<?xml version='1.0' encoding='utf-8' ?>";
?>
<wsdl:definitions
  xmlns:wsdlsoap="http://schemas.xmlsoap.org/wsdl/soap/"
  xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/"
  xmlns:xsd="http://www.w3.org/2001/XMLSchema"
  xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/">

<wsdl:types>
    <schema xmlns="http://www.w3.org/2001/XMLSchema">
        <element name="verifyDataRequest">
            <complexType>
                <sequence>
                    <element name="token" type="xsd:string"/>
                    <element name="Matrikelnummer" nillable="true" type="xsd:string"/>
                    <element name="Name" nillable="true" type="xsd:string"/>
                    <element name="Vorname" nillable="true" type="xsd:string"/>
                    <element name="Geburtsdatum" nillable="true" type="xsd:date"/>
                    <element name="Postleitzahl" nillable="true" type="xsd:string"/>
                    <element name="Semesterkuerzel" nillable="true" type="xsd:string"/>
                </sequence>
            </complexType>
        </element>

        <element name="verifyDataResponse">
            <complexType>
                <sequence>
                    <element name="result" type="xsd:string"/>
                    <element name="fehlerCode" minOccurs="0" maxOccurs="1" type="xsd:string"/>
                </sequence>
            </complexType>
        </element>
    </schema>
</wsdl:types>

<wsdl:message name="verifyDataRequest">
    <wsdl:part name="verifyDataRequest" element="verifyDataRequest"/>
</wsdl:message>

<wsdl:message name="verifyDataResponse">
    <wsdl:part name="verifyDataResponse" element="verifyDataResponse"/>
</wsdl:message>

<wsdl:portType name="SemesterTicketServicePortType">
    <wsdl:operation name="verifyData" parameterOrder="verifyDataRequest">
        <wsdl:input name="verifyDataRequest" message="verifyDataRequest"/>
        <wsdl:output name="verifyDataResponse" message="verifyDataResponse"/>
    </wsdl:operation>
</wsdl:portType>

<wsdl:binding name="SemesterTicketServicePortSoapBinding" type="SemesterTicketServicePortType">
    <wsdlsoap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
    <wsdl:operation name="verifyData">
        <wsdlsoap:operation soapAction="verifyData"/>
            <wsdl:input name="verifyDataRequest">
                <wsdlsoap:body use="literal"/>
            </wsdl:input>
            <wsdl:output name="verifyDataResponse">
                <wsdlsoap:body use="literal"/>
            </wsdl:output>
    </wsdl:operation>
</wsdl:binding>

<wsdl:service name="SemesterTicketService">
    <wsdl:port name="SemesterTicketServicePort" binding="SemesterTicketServicePortSoapBinding">
        <wsdlsoap:address location="<?php echo APP_ROOT."soap/semesterticket.soap.php?".microtime(true);?>" />
    </wsdl:port>
</wsdl:service>
</wsdl:definitions>
