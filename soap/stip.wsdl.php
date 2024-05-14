<?php
require_once('../config/vilesci.config.inc.php');
header("Content-type: text/plain");
echo "<?xml version='1.0' encoding='utf-8' ?>";
?>
<wsdl:definitions xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
	xmlns:mime="http://schemas.xmlsoap.org/wsdl/mime/"
	xmlns:tns="http://www.fhr.ac.at/BISWS/STIP/WebServices/Services/STIPServiceDecentralized"
	xmlns:s="http://www.w3.org/2001/XMLSchema"
	xmlns:http="http://schemas.xmlsoap.org/wsdl/http/"
	targetNamespace="http://www.fhr.ac.at/BISWS/STIP/WebServices/Services/STIPServiceDecentralized"
	xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/">
  <wsdl:types>
	<s:schema elementFormDefault="qualified" targetNamespace="http://www.fhr.ac.at/BISWS/STIP/WebServices/Services/STIPServiceDecentralized">
	  <s:element name="GetStipendienbezieherStip">
		<s:complexType>
		  <s:sequence>
			<s:element minOccurs="0" maxOccurs="1" name="userName" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="passWord" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="anfrageDaten" type="tns:GetStipendienbezieherStipRequest"/>
		  </s:sequence>
		</s:complexType>
	  </s:element>
	  <s:complexType name="GetStipendienbezieherStipRequest">
		<s:sequence>
		  <s:element minOccurs="0" maxOccurs="1" name="ErhKz" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="AnfragedatenID" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="Stipendiumsbezieher" type="tns:ArrayOfStipendiumsbezieherAnfrage"/>
		</s:sequence>
	  </s:complexType>
	  <s:complexType name="ArrayOfStipendiumsbezieherAnfrage">
		<s:sequence>
		  <s:element minOccurs="0" maxOccurs="unbounded" name="StipendiumsbezieherAnfrage" nillable="true" type="tns:StipendiumsbezieherAnfrage"/>
		</s:sequence>
	  </s:complexType>
	  <s:complexType name="StipendiumsbezieherAnfrage">
		<s:sequence>
		  <s:element minOccurs="0" maxOccurs="1" name="Semester" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="Studienjahr" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="PersKz" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="Matrikelnummer" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="StgKz" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="SVNR" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="Familienname" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="Vorname" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="Typ" type="s:string"/>
		</s:sequence>
	  </s:complexType>
	  <s:complexType name="StipendiumsbezieherAntwort">
		<s:complexContent mixed="false">
		  <s:extension base="tns:StipendiumsbezieherAnfrage">
			<s:sequence>
			  <s:element minOccurs="0" maxOccurs="1" name="PersKz_Antwort" type="s:string"/>
			  <s:element minOccurs="0" maxOccurs="1" name="Matrikelnummer_Antwort" type="s:string"/>
			  <s:element minOccurs="0" maxOccurs="1" name="StgKz_Antwort" type="s:string"/>
			  <s:element minOccurs="0" maxOccurs="1" name="SVNR_Antwort" type="s:string"/>
			  <s:element minOccurs="0" maxOccurs="1" name="Familienname_Antwort" type="s:string"/>
			  <s:element minOccurs="0" maxOccurs="1" name="Vorname_Antwort" type="s:string"/>
			  <s:element minOccurs="0" maxOccurs="1" name="Ausbildungssemester" type="s:string"/>
			  <s:element minOccurs="0" maxOccurs="1" name="StudStatusCode" type="s:string"/>
			  <s:element minOccurs="1" maxOccurs="1" name="BeendigungsDatum" type="s:string"/>
			  <s:element minOccurs="1" maxOccurs="1" name="VonNachPersKz" type="s:string"/>
			  <s:element minOccurs="0" maxOccurs="1" name="Studienbeitrag" type="s:string"/>
			  <s:element minOccurs="0" maxOccurs="1" name="Inskribiert" type="s:string"/>
			  <s:element minOccurs="0" maxOccurs="1" name="Erfolg" type="s:string"/>
			  <s:element minOccurs="0" maxOccurs="1" name="OrgFormTeilCode" type="s:string"/>
			  <s:element minOccurs="0" maxOccurs="1" name="AntwortStatusCode" type="s:string"/>
			</s:sequence>
		  </s:extension>
		</s:complexContent>
	  </s:complexType>
	  <s:element name="GetStipendienbezieherStipResponse">
		<s:complexType>
		  <s:sequence>
			<s:element minOccurs="0" maxOccurs="1" name="GetStipendienbezieherStipResult" type="tns:GetStipendienbezieherStipResponse"/>
		  </s:sequence>
		</s:complexType>
	  </s:element>
	  <s:complexType name="GetStipendienbezieherStipResponse">
		<s:sequence>
		  <s:element minOccurs="0" maxOccurs="1" name="ErhKz" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="AnfragedatenID" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="Stipendiumsbezieher" type="tns:ArrayOfStipendiumsbezieherAntwort"/>
		</s:sequence>
	  </s:complexType>
	  <s:complexType name="ArrayOfStipendiumsbezieherAntwort">
		<s:sequence>
		  <s:element minOccurs="0" maxOccurs="unbounded" name="StipendiumsbezieherAntwort" nillable="true" type="tns:StipendiumsbezieherAntwort"/>
		</s:sequence>
	  </s:complexType>
	  <s:element name="SendStipendienbezieherStipError">
		<s:complexType>
		  <s:sequence>
			<s:element minOccurs="0" maxOccurs="1" name="userName" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="passWord" type="s:string"/>
			<s:element minOccurs="0" maxOccurs="1" name="errorReport" type="tns:SendStipendienbezieherStipErrorRequest"/>
		  </s:sequence>
		</s:complexType>
	  </s:element>
	  <s:complexType name="SendStipendienbezieherStipErrorRequest">
		<s:sequence>
		  <s:element minOccurs="0" maxOccurs="1" name="ErhKz" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="StateCode" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="StateMessage" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="ErrorStatusCode" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="JobID" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="ErrorContent" type="tns:ArrayOfErrorContentItem"/>
		</s:sequence>
	  </s:complexType>
	  <s:complexType name="ArrayOfErrorContentItem">
		<s:sequence>
		  <s:element minOccurs="0" maxOccurs="unbounded" name="ErrorContentItem" nillable="true" type="tns:ErrorContentItem"/>
		</s:sequence>
	  </s:complexType>
	  <s:complexType name="ErrorContentItem">
		<s:sequence>
		  <s:element minOccurs="0" maxOccurs="1" name="ErrorNumber" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="KeyAttribute" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="KeyValues" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="CheckAttribute" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="CheckValue" type="s:string"/>
		  <s:element minOccurs="0" maxOccurs="1" name="ErrorText" type="s:string"/>
		</s:sequence>
	  </s:complexType>
	  <s:element name="SendStipendienbezieherStipErrorResponse">
		<s:complexType/>
	  </s:element>
	</s:schema>
  </wsdl:types>
  <wsdl:message name="GetStipendienbezieherStipSoapIn">
	<wsdl:part name="parameters" element="tns:GetStipendienbezieherStip"/>
  </wsdl:message>
  <wsdl:message name="GetStipendienbezieherStipSoapOut">
	<wsdl:part name="parameters" element="tns:GetStipendienbezieherStipResponse"/>
  </wsdl:message>
  <wsdl:message name="SendStipendienbezieherStipErrorSoapIn">
	<wsdl:part name="parameters" element="tns:SendStipendienbezieherStipError"/>
  </wsdl:message>
  <wsdl:message name="SendStipendienbezieherStipErrorSoapOut">
	<wsdl:part name="parameters" element="tns:SendStipendienbezieherStipErrorResponse"/>
  </wsdl:message>
  <wsdl:portType name="STIPServiceDecentralizedSoap">
	<wsdl:operation name="GetStipendienbezieherStip">
	  <wsdl:input message="tns:GetStipendienbezieherStipSoapIn"/>
	  <wsdl:output message="tns:GetStipendienbezieherStipSoapOut"/>
	</wsdl:operation>
	<wsdl:operation name="SendStipendienbezieherStipError">
	  <wsdl:input message="tns:SendStipendienbezieherStipErrorSoapIn"/>
	  <wsdl:output message="tns:SendStipendienbezieherStipErrorSoapOut"/>
	</wsdl:operation>
  </wsdl:portType>
  <wsdl:binding name="STIPServiceDecentralizedSoap" type="tns:STIPServiceDecentralizedSoap">
	<soap:binding transport="http://schemas.xmlsoap.org/soap/http"/>
	<wsdl:operation name="GetStipendienbezieherStip">
	  <soap:operation soapAction="http://www.fhr.ac.at/BISWS/STIP/WebServices/Services/STIPServiceDecentralized/GetStipendienbezieherStip" style="document"/>
	  <wsdl:input>
		<soap:body use="literal"/>
	  </wsdl:input>
	  <wsdl:output>
		<soap:body use="literal"/>
	  </wsdl:output>
	</wsdl:operation>
	<wsdl:operation name="SendStipendienbezieherStipError">
	  <soap:operation soapAction="http://www.fhr.ac.at/BISWS/STIP/WebServices/Services/STIPServiceDecentralized/SendStipendienbezieherStipError" style="document"/>
	  <wsdl:input>
		<soap:body use="literal"/>
	  </wsdl:input>
	  <wsdl:output>
		<soap:body use="literal"/>
	  </wsdl:output>
	</wsdl:operation>
  </wsdl:binding>
  <wsdl:service name="STIPServiceDecentralized">
	<wsdl:port name="STIPServiceDecentralizedSoap" binding="tns:STIPServiceDecentralizedSoap">
	  <soap:address location="<?php echo APP_ROOT."/soap/stip.soap.php?".microtime(true);?>"/>
	</wsdl:port>
  </wsdl:service>
</wsdl:definitions>
