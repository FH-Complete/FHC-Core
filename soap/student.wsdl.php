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

    <wsdl:message name="GetStudentFromUidRequest">
        <wsdl:part minOccurs="0" maxOccurs="1" name="student_uid" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

    <wsdl:message name="GetStudentFromUidResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="GetStudentFromUid" type="tns:Student"/>
    </wsdl:message>

    <wsdl:message name="GetStudentFromMatrikelnummerRequest">
        <wsdl:part minOccurs="0" maxOccurs="1" name="student_uid" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

    <wsdl:message name="GetStudentFromMatrikelnummerResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="GetStudentFromUid" type="tns:Student"/>
    </wsdl:message>


    <wsdl:message name="GetStudentFromStudiengangRequest">
        <wsdl:part minOccurs="0" maxOccurs="1" name="studiengang" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="semester" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="verband" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="gruppe" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

    <wsdl:message name="GetStudentFromStudiengangResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="GetStudentFromStudiengang" type="tns:ArrayOfStudentItem"/>
    </wsdl:message>

    <s:complexType name="ArrayOfStudentItem">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="unbounded" name="StudentItem" nillable="true" type="tns:Student"/>
        </s:sequence>
    </s:complexType>

    <s:complexType name="Student">
        <s:element minOccurs="0" maxOccurs="1" name="studiengang_kz" type="s:string"/>
        <s:element minOccurs="0" maxOccurs="1" name="person_id" type="s:string"/>
        <s:element minOccurs="0" maxOccurs="1" name="semester" type="s:string"/>
        <s:element minOccurs="0" maxOccurs="1" name="verband" type="s:string"/>
        <s:element minOccurs="0" maxOccurs="1" name="gruppe" type="s:string"/>
        <s:element minOccurs="0" maxOccurs="1" name="vorname" type="s:string"/>
        <s:element minOccurs="0" maxOccurs="1" name="nachname" type="s:string"/>
        <s:element minOccurs="0" maxOccurs="1" name="uid" type="s:string"/>
        <s:element minOccurs="0" maxOccurs="1" name="status" type="s:string"/>
        <s:element minOccurs="0" maxOccurs="1" name="personenkennzeichen" type="s:string"/>
        <s:element minOccurs="0" maxOccurs="1" name="email" type="s:string"/>
    </s:complexType>

    <s:complexType name="GetAuthentifizierung">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="1" name="username" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="passwort" type="s:string"/>
        </s:sequence>
    </s:complexType>

    <wsdl:portType name="ConfigPortType">
        <wsdl:operation name="getStudentFromUid">
            <wsdl:input message="tns:GetStudentFromUidRequest"/>
            <wsdl:output message="tns:GetStudentFromUidResponse"/>
        </wsdl:operation>
        <wsdl:operation name="getStudentFromMatrikelnummer">
            <wsdl:input message="tns:GetStudentFromMatrikelnummerRequest"/>
            <wsdl:output message="tns:GetStudentFromMatrikelnummerResponse"/>
        </wsdl:operation>
        <wsdl:operation name="getStudentFromStudiengang">
            <wsdl:input message="tns:GetStudentFromStudiengangRequest"/>
            <wsdl:output message="tns:GetStudentFromStudiengangResponse"/>
        </wsdl:operation>
    </wsdl:portType>

    <wsdl:binding name="ConfigBinding" type="tns:ConfigPortType">
        <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http" />
        <wsdl:operation name="getStudentFromUid">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/getStudentFromUid";?>"  />
            <wsdl:input>
               <soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
                <soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
        <wsdl:operation name="getStudentFromMatrikelnummer">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/getStudentFromMatrikelnummer";?>"  />
            <wsdl:input>
               <soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
                <soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
        <wsdl:operation name="getStudentFromStudiengang">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/getStudentFromStudiengang";?>"  />
            <wsdl:input>
               <soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
                <soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
    </wsdl:binding>

    <wsdl:service name="Student">
        <wsdl:port name="ConfigWebservicePort" binding="tns:ConfigBinding">
            <soap:address location="<?php echo APP_ROOT."/soap/student.soap.php?".microtime(true);?>"/>
        </wsdl:port>
    </wsdl:service>
</wsdl:definitions>
