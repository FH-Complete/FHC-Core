<?php 
require_once('../config/vilesci.config.inc.php');
header("Content-type: text/plain");
echo "<?xml version='1.0' encoding='utf-8' ?>";
?>

<wsdl:definitions name="Projekttask" 
targetNamespace="http://localhost/soap/" 
xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" 
xmlns:tns="http://localhost/soap/" 
xmlns:xsd="http://www.w3.org/2001/XMLSchema"    
xmlns:xsd1="http://localhost/soap/projekttask.xsd"    
xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/">

	<wsdl:message name="SaveProjekttaskRequest">
		<wsdl:part name="projekttask_id" type="xsd:string"></wsdl:part>
		<wsdl:part name="projektphase_id" type="xsd:string"></wsdl:part>
		<wsdl:part name="bezeichnung" type="xsd:string"></wsdl:part>
		<wsdl:part name="beschreibung" type="xsd:string"></wsdl:part>
		<wsdl:part name="aufwand" type="xsd:string"></wsdl:part>
		<wsdl:part name="mantis_id" type="xsd:string"></wsdl:part>
		<wsdl:part name="insertamum" type="xsd:string"></wsdl:part>
		<wsdl:part name="insertvon" type="xsd:string"></wsdl:part>
		<wsdl:part name="updateamum" type="xsd:string"></wsdl:part>
		<wsdl:part name="updatevon" type="xsd:string"></wsdl:part>
       </wsdl:message>

 	<wsdl:message name="SaveProjekttaskResponse">
       <wsdl:part name="message" type="xsd:string"></wsdl:part>
  	</wsdl:message>
   
 <wsdl:portType name="ConfigPortType" >
       <wsdl:operation name="saveProjekttask">
           <wsdl:input message="tns:SaveProjekttaskRequest"></wsdl:input>
           <wsdl:output message="tns:SaveProjekttaskResponse"></wsdl:output>        
       </wsdl:operation>
   </wsdl:portType>

   <wsdl:binding name="ConfigBinding" type="tns:ConfigPortType">
       <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http" />
       <wsdl:operation name="saveProjekttask">
           <soap:operation soapAction="<?php echo APP_ROOT."soap/saveProjekttask";?>" />
           <wsdl:input> 
               <soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
           </wsdl:input>
           <wsdl:output>
               <soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
           </wsdl:output>
       </wsdl:operation> 
   </wsdl:binding>  
 
   <wsdl:service name="Projekttask">
       <wsdl:port name="ConfigWebservicePort" binding="tns:ConfigBinding">
           <soap:address location="<?php echo APP_ROOT."soap/projekttask.soap.php";?>"/>
       </wsdl:port>
   </wsdl:service>
</wsdl:definitions>
