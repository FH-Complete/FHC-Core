<?php 
require_once('../config/vilesci.config.inc.php');
header("Content-type: text/plain");
echo "<?xml version='1.0' encoding='utf-8' ?>";
?>

<wsdl:definitions name="Projektphase" 
targetNamespace="http://www.technikum-wien.at/soap/" 
xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" 
xmlns:tns="http://www.technikum-wien.at/soap/" 
xmlns:xsd="http://www.w3.org/2001/XMLSchema"    
xmlns:xsd1="http://localhost/soap/projektphase.xsd"    
xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/">

       
	<wsdl:message name="SaveProjektphaseRequest">
		<wsdl:part name="username" type="xsd:string" minOccurs="0"></wsdl:part>
		<wsdl:part name="passwort" type="xsd:string" minOccurs="0"></wsdl:part>  	
		<wsdl:part name="phase" type="tns:phase"></wsdl:part>
	</wsdl:message>
           
	<xsd:complexType name="phase">
		<xsd:all>	
			<wsdl:part name="projektphase_id" type="xsd:string"></wsdl:part>
			<wsdl:part name="projekt_kurzbz" type="xsd:string"></wsdl:part>
			<wsdl:part name="projektphase_fk" type="xsd:string"></wsdl:part>
			<wsdl:part name="bezeichnung" type="xsd:string"></wsdl:part>
			<wsdl:part name="typ" type="xsd:string"></wsdl:part>
			<wsdl:part name="ressource_id" type="xsd:string"></wsdl:part>
			<wsdl:part name="beschreibung" type="xsd:string"></wsdl:part>
			<wsdl:part name="start" type="xsd:string"></wsdl:part>
			<wsdl:part name="ende" type="xsd:string"></wsdl:part>
			<wsdl:part name="budget" type="xsd:string"></wsdl:part>
			<wsdl:part name="personentage" type="xsd:string"></wsdl:part>
         <wsdl:part name="farbe" type="xsd:string"></wsdl:part>
			<wsdl:part name="user" type="xsd:string"></wsdl:part>
			<wsdl:part name="neu" type="xsd:boolean"></wsdl:part>
            <wsdl:part name="zeitaufzeichnung" type="xsd:string"></wsdl:part>
		</xsd:all>
	</xsd:complexType>

	<wsdl:message name="SaveProjektphaseResponse">
		<wsdl:part name="message" type="xsd:string"></wsdl:part>
	</wsdl:message>
	
	<wsdl:message name="DeleteProjektphaseRequest">
		<wsdl:part name="username" type="xsd:string" minOccurs="0"></wsdl:part>
		<wsdl:part name="passwort" type="xsd:string" minOccurs="0"></wsdl:part>  	
		<wsdl:part name="projektphase_id" type="xsd:string"></wsdl:part>
	</wsdl:message>
	<wsdl:message name="DeleteProjektphaseResponse">
		<wsdl:part name="message" type="xsd:string"></wsdl:part>
	</wsdl:message>
   
	<wsdl:portType name="ConfigPortType" >
		<wsdl:operation name="saveProjektphase">
			<wsdl:input message="tns:SaveProjektphaseRequest"></wsdl:input>
			<wsdl:output message="tns:SaveProjektphaseResponse"></wsdl:output>        
		</wsdl:operation>
		<wsdl:operation name="deleteProjektphase">
			<wsdl:input message="tns:DeleteProjektphaseRequest"></wsdl:input>
			<wsdl:output message="tns:DeleteProjektphaseResponse"></wsdl:output>        
		</wsdl:operation>
	</wsdl:portType>

	<wsdl:binding name="ConfigBinding" type="tns:ConfigPortType">
		<soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http" />
		<wsdl:operation name="saveProjektphase">
			<soap:operation soapAction="<?php echo APP_ROOT."soap/saveProjektphase";?>" />
			<wsdl:input> 
				<soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</wsdl:input>
			<wsdl:output>
				<soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</wsdl:output>
		</wsdl:operation> 
		<wsdl:operation name="deleteProjektphase">
			<soap:operation soapAction="<?php echo APP_ROOT."soap/deleteProjektphase";?>" />
			<wsdl:input> 
				<soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</wsdl:input>
			<wsdl:output>
				<soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
			</wsdl:output>
		</wsdl:operation> 
	</wsdl:binding>
 
	<wsdl:service name="Projektphase">
		<wsdl:port name="ConfigWebservicePort" binding="tns:ConfigBinding">
			<soap:address location="<?php echo APP_ROOT."soap/projektphase.soap.php";?>"/>
		</wsdl:port>
	</wsdl:service>
</wsdl:definitions>
