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

    <wsdl:message name="GetLVPlanFromUserRequest">
        <wsdl:part minOccurs="1" maxOccurs="1" name="uid" type="s:string"/>
		<wsdl:part minOccurs="1" maxOccurs="1" name="von" type="s:string"/>
		<wsdl:part minOccurs="1" maxOccurs="1" name="bis" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

     <wsdl:message name="GetLVPlanFromUserResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="GetLVPlanFromUser" type="tns:ArrayOfLVPlan"/>
    </wsdl:message>

    <s:complexType name="GetAuthentifizierung">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="1" name="username" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="passwort" type="s:string"/>
        </s:sequence>
    </s:complexType>

    <s:complexType name="ArrayOfLVPlan">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="unbounded" name="lvplan" type="tns:LVPlanItem"/>
        </s:sequence>
    </s:complexType>

    <s:complexType name="LVPlanItem">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="1" name="anmerkung" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="lehrform" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="titel" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="studiengang_kz" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="semester" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="stunde" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="datum" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="stundenplan_id" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="lehrveranstaltung_id" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="lehreinheit_id" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="institut" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="farbe" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="unbounded" name="lektor" type="tns:Lektor"/>
			<s:element minOccurs="0" maxOccurs="unbounded" name="gruppe" type="tns:Gruppe"/>
			<s:element minOccurs="0" maxOccurs="unbounded" name="orte" type="s:Ort"/>
        </s:sequence>
    </s:complexType>

    <s:complexType name="Lektor">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="1" name="uid" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="vorname" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="nachname" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="titelpre" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="titelpost" type="s:string"/>
        </s:sequence>
    </s:complexType>

    <s:complexType name="Ort">
        <s:sequence>
            <s:element minOccurs="0" maxOccurs="1" name="ort_kurzbz" type="s:string"/>
        </s:sequence>
    </s:complexType>

    <s:complexType name="Gruppe">
        <s:sequence>
			<s:element minOccurs="0" maxOccurs="1" name="name" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="studiengang_kz" type="s:string"/>
            <s:element minOccurs="0" maxOccurs="1" name="semester" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="verband" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="gruppe" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="gruppe_kurzbz" type="s:string"/>
        </s:sequence>
    </s:complexType>

	<wsdl:message name="GetLVPlanFromLVRequest">
		<wsdl:part minOccurs="1" maxOccurs="1" name="lehrveranstaltung_id" type="s:string"/>
		<wsdl:part minOccurs="1" maxOccurs="1" name="studiensemester_kurzbz" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

    <wsdl:message name="GetLVPlanFromLVResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="LVPlan" type="tns:ArrayOfLVPlan"/>
    </wsdl:message>

   	<wsdl:message name="GetLVPlanFromStgRequest">
		<wsdl:part minOccurs="1" maxOccurs="1" name="studiengang_kz" type="s:string"/>
		<wsdl:part minOccurs="1" maxOccurs="1" name="semester" type="s:string"/>
		<wsdl:part minOccurs="0" maxOccurs="1" name="verband" type="s:string"/>
		<wsdl:part minOccurs="0" maxOccurs="1" name="gruppe" type="s:string"/>
		<wsdl:part minOccurs="0" maxOccurs="1" name="gruppe_kurzbz" type="s:string"/>
		<wsdl:part minOccurs="1" maxOccurs="1" name="von" type="s:string"/>
		<wsdl:part minOccurs="1" maxOccurs="1" name="bis" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

    <wsdl:message name="GetLVPlanFromStgResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="LVPlan" type="tns:ArrayOfLVPlan"/>
    </wsdl:message>

	<wsdl:message name="GetLVPlanFromOrtRequest">
		<wsdl:part minOccurs="1" maxOccurs="1" name="ort_kurzbz" type="s:string"/>
		<wsdl:part minOccurs="1" maxOccurs="1" name="von" type="s:string"/>
		<wsdl:part minOccurs="1" maxOccurs="1" name="bis" type="s:string"/>
        <wsdl:part minOccurs="0" maxOccurs="1" name="authentifizierung" type="tns:GetAuthentifizierung"/>
    </wsdl:message>

    <wsdl:message name="GetLVPlanFromOrtResponse">
        <wsdl:part minOccurs="0" maxOccurs="1" name="LVPlan" type="tns:ArrayOfLVPlan"/>
    </wsdl:message>

    <wsdl:portType name="ConfigPortType">
        <wsdl:operation name="getLVPlanFromUser">
            <wsdl:input message="tns:GetLVPlanFromUserRequest"/>
            <wsdl:output message="tns:GetLVPlanFromUserResponse"/>
        </wsdl:operation>
        <wsdl:operation name="getLVPlanFromLV">
            <wsdl:input message="tns:GetLVPlanFromLVRequest"/>
            <wsdl:output message="tns:GetLVPlanFromLVResponse"/>
        </wsdl:operation>
        <wsdl:operation name="getLVPlanFromStg">
            <wsdl:input message="tns:GetLVPlanFromStgRequest"/>
            <wsdl:output message="tns:GetLVPlanFromStgResponse"/>
        </wsdl:operation>
        <wsdl:operation name="getLVPlanFromOrt">
            <wsdl:input message="tns:GetLVPlanFromOrtRequest"/>
            <wsdl:output message="tns:GetLVPlanFromOrtResponse"/>
        </wsdl:operation>
    </wsdl:portType>

    <wsdl:binding name="ConfigBinding" type="tns:ConfigPortType">
        <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http" />
        <wsdl:operation name="getLVPlanFromUser">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/getLVPlanFromUser";?>"  />
            <wsdl:input>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
        <wsdl:operation name="getLVPlanFromLV">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/getLVPlanFromLV";?>"  />
            <wsdl:input>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
         <wsdl:operation name="getLVPlanFromStg">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/getLVPlanFromStg";?>"  />
            <wsdl:input>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
         <wsdl:operation name="getLVPlanFromOrt">
            <soap:operation soapAction="<?php echo APP_ROOT."soap/getLVPlanFromOrt";?>"  />
            <wsdl:input>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:input>
            <wsdl:output>
				<soap:body use="encoded" namespace="http://technikum-wien.at" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
            </wsdl:output>
        </wsdl:operation>
    </wsdl:binding>

    <wsdl:service name="LVPlan">
        <wsdl:port name="ConfigWebservicePort" binding="tns:ConfigBinding">
            <soap:address location="<?php echo APP_ROOT."soap/lvplan.soap.php?".microtime(true);?>"/>
        </wsdl:port>
    </wsdl:service>
</wsdl:definitions>
