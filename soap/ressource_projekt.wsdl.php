<?php 
require_once('../config/vilesci.config.inc.php');
header("Content-type: text/plain");
echo "<?xml version='1.0' encoding='utf-8' ?>";
?>

<wsdl:definitions name="ProjektRessource" 
targetNamespace="http://www.technikum-wien.at/soap/" 
xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" 
xmlns:tns="http://www.technikum-wien.at/soap/" 
xmlns:xsd="http://www.w3.org/2001/XMLSchema"    
xmlns:xsd1="http://localhost/soap/projektressource.xsd"    
xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/">

    <wsdl:message name="SaveProjektRessourceRequest">
		<wsdl:part name="username" type="xsd:string" minOccurs="0"></wsdl:part>
		<wsdl:part name="passwort" type="xsd:string" minOccurs="0"></wsdl:part>  	
		<wsdl:part name="projektRessource" type="tns:projektRessource"></wsdl:part>
    </wsdl:message>

	<xsd:complexType name="projektRessource">
		<xsd:all>	
			<wsdl:part name="projekt_ressource_id" type="xsd:int"></wsdl:part>
			<wsdl:part name="projektphase_id" type="xsd:int"></wsdl:part>
			<wsdl:part name="projekt_kurzbz" type="xsd:string"></wsdl:part>
			<wsdl:part name="ressource_id" type="xsd:int"></wsdl:part>
			<wsdl:part name="funktion_kurzbz" type="xsd:string"></wsdl:part>
			<wsdl:part name="beschreibung" type="xsd:string"></wsdl:part>
			<wsdl:part name="aufwand" type="xsd:string"></wsdl:part>
		</xsd:all>
	</xsd:complexType>

 	<wsdl:message name="SaveProjektRessourceResponse">
       <wsdl:part name="message" type="xsd:string"></wsdl:part>
  	</wsdl:message>
   
    <wsdl:message name="DeleteProjektRessourceRequest">
		<wsdl:part name="username" type="xsd:string" minOccurs="0"></wsdl:part>
		<wsdl:part name="passwort" type="xsd:string" minOccurs="0"></wsdl:part>  	
		<wsdl:part name="projektRessource" type="xsd:projektRessource"></wsdl:part>
    </wsdl:message>
    
    <wsdl:message name="DeleteProjektRessourceResponse">
       <wsdl:part name="message" type="xsd:string"></wsdl:part>
  	</wsdl:message>
    
 <wsdl:portType name="ConfigPortType" >
       <wsdl:operation name="saveProjektRessource">
           <wsdl:input message="tns:SaveProjektRessourceRequest"></wsdl:input>
           <wsdl:output message="tns:SaveProjektRessourceResponse"></wsdl:output>        
       </wsdl:operation>
        <wsdl:operation name="deleteProjektRessource">
           <wsdl:input message="tns:DeleteProjektRessourceRequest"></wsdl:input>
           <wsdl:output message="tns:DeleteProjektRessourceResponse"></wsdl:output>        
       </wsdl:operation>
   </wsdl:portType>

   <wsdl:binding name="ConfigBinding" type="tns:ConfigPortType">
       <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http" />
       <wsdl:operation name="saveProjektRessource">
           <soap:operation soapAction="<?php echo APP_ROOT."soap/saveProjektRessource";?>" />
           <wsdl:input> 
               <soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
           </wsdl:input>
           <wsdl:output>
               <soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
           </wsdl:output>
       </wsdl:operation> 
        <wsdl:operation name="deleteProjektRessource">
           <soap:operation soapAction="<?php echo APP_ROOT."soap/deleteProjektRessource";?>" />
           <wsdl:input> 
               <soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
           </wsdl:input>
           <wsdl:output>
               <soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
           </wsdl:output>
       </wsdl:operation> 
   </wsdl:binding>  
 
   <wsdl:service name="ProjektRessource">
       <wsdl:port name="ConfigWebservicePort" binding="tns:ConfigBinding">
           <soap:address location="<?php echo APP_ROOT."soap/ressource_projekt.soap.php";?>"/>
       </wsdl:port>
   </wsdl:service>
</wsdl:definitions>