<?php 
require_once('../config/vilesci.config.inc.php');
header("Content-type: text/plain");
echo "<?xml version='1.0' encoding='utf-8' ?>";
?>

<wsdl:definitions name="Ressource" 
targetNamespace="http://www.technikum-wien.at/soap/" 
xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" 
xmlns:tns="http://www.technikum-wien.at/soap/" 
xmlns:xsd="http://www.w3.org/2001/XMLSchema"    
xmlns:xsd1="http://localhost/soap/ressource.xsd"    
xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/">

	<wsdl:message name="SaveRessourceRequest">
		<wsdl:part name="username" type="xsd:string" minOccurs="0"></wsdl:part>
		<wsdl:part name="passwort" type="xsd:string" minOccurs="0"></wsdl:part>  	
		<wsdl:part name="ressource" type="tns:ressource"></wsdl:part>
    </wsdl:message>

	<xsd:complexType name="ressource">
		<xsd:all>	
			<wsdl:part name="ressource_id" type="xsd:int"></wsdl:part>
			<wsdl:part name="bezeichnung" type="xsd:string"></wsdl:part>
			<wsdl:part name="beschreibung" type="xsd:string"></wsdl:part>
			<wsdl:part name="mitarbeiter_uid" type="xsd:string"></wsdl:part>
			<wsdl:part name="student_uid" type="xsd:string"></wsdl:part>
			<wsdl:part name="betriebsmittel_id" type="xsd:int"></wsdl:part>
			<wsdl:part name="firma_id" type="xsd:int"></wsdl:part>
		</xsd:all>
	</xsd:complexType>

 	<wsdl:message name="SaveRessourceResponse">
       <wsdl:part name="message" type="xsd:string"></wsdl:part>
  	</wsdl:message>
   
	<wsdl:portType name="ConfigPortType" >
		<wsdl:operation name="saveRessource">
			<wsdl:input message="tns:SaveRessourceRequest"></wsdl:input>
			<wsdl:output message="tns:SaveRessourceResponse"></wsdl:output>        
		</wsdl:operation>
	</wsdl:portType>

	<wsdl:binding name="ConfigBinding" type="tns:ConfigPortType">
		<soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http" />
		<wsdl:operation name="saveRessource">
			<soap:operation soapAction="<?php echo APP_ROOT."soap/saveRessource";?>" />
			<wsdl:input> 
				<soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</wsdl:input>
			<wsdl:output>
				<soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</wsdl:output>
		</wsdl:operation> 
	</wsdl:binding>  
 
	<wsdl:service name="Ressource">
		<wsdl:port name="ConfigWebservicePort" binding="tns:ConfigBinding">
			<soap:address location="<?php echo APP_ROOT."soap/ressource.soap.php";?>"/>
		</wsdl:port>
	</wsdl:service>
</wsdl:definitions>