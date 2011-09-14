<?php 
require_once('../config/vilesci.config.inc.php');
header("Content-type: text/plain");
echo "<?xml version='1.0' encoding='utf-8' ?>";
?>

<wsdl:definitions name="Projekt" 
targetNamespace="http://localhost/soap/" 
xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" 
xmlns:tns="http://localhost/soap/" 
xmlns:xsd="http://www.w3.org/2001/XMLSchema"    
xmlns:xsd1="http://localhost/soap/projekt.xsd"    
xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/">


	<wsdl:message name="SaveProjektRequest">
       <wsdl:part name="projekt_kurzbz" type="xsd:string"></wsdl:part>
       <wsdl:part name="nummer" type="xsd:string"></wsdl:part>
       <wsdl:part name="titel" type="xsd:string"></wsdl:part>
       <wsdl:part name="beschreibung" type="xsd:string"></wsdl:part>
       <wsdl:part name="beginn" type="xsd:string"></wsdl:part>
       <wsdl:part name="ende" type="xsd:string"></wsdl:part>
       <wsdl:part name="oe_kurzbz" type="xsd:string"></wsdl:part>
       <wsdl:part name="neu" type="xsd:boolean"></wsdl:part>
  	</wsdl:message>
  	<wsdl:message name="SaveProjektResponse">
       <wsdl:part name="message" type="xsd:string"></wsdl:part>
  	</wsdl:message>
	<wsdl:message name="SaveProjektdokumentZuordnungRequest">
       <wsdl:part name="projekt_kurzbz" type="xsd:string"></wsdl:part>
       <wsdl:part name="projektphase_id" type="xsd:string"></wsdl:part>
       <wsdl:part name="dms_id" type="xsd:string"></wsdl:part>
  	</wsdl:message>
 	<wsdl:message name="SaveProjektdokumentZuordnungResponse">
       <wsdl:part name="message" type="xsd:string"></wsdl:part>
  	</wsdl:message>
   
 <wsdl:portType name="ConfigPortType" >
       <wsdl:operation name="saveProjekt">
           <wsdl:input message="tns:SaveProjektRequest"></wsdl:input>
           <wsdl:output message="tns:SaveProjektResponse"></wsdl:output>        
       </wsdl:operation>
       <wsdl:operation name="saveProjektdokumentZuordnung">
           <wsdl:input message="tns:SaveProjektdokumentZuordnungRequest"></wsdl:input>
           <wsdl:output message="tns:SaveProjektdokumentZuordnungResponse"></wsdl:output>        
       </wsdl:operation>
   </wsdl:portType>

   <wsdl:binding name="ConfigBinding" type="tns:ConfigPortType">
       <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http" />
       <wsdl:operation name="saveProjekt">
           <soap:operation soapAction="<?php echo APP_ROOT."soap/saveProjekt";?>" />
           <wsdl:input> 
               <soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
           </wsdl:input>
           <wsdl:output>
               <soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
           </wsdl:output>
       </wsdl:operation> 
       <wsdl:operation name="saveProjektdokumentZuordnung">
           <soap:operation soapAction="<?php echo APP_ROOT."soap/saveProjektdokumentZuordnung";?>" />
           <wsdl:input> 
               <soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
           </wsdl:input>
           <wsdl:output>
               <soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
           </wsdl:output>
       </wsdl:operation> 
   </wsdl:binding>  
 
   <wsdl:service name="Projekt">
       <wsdl:port name="ConfigWebservicePort" binding="tns:ConfigBinding">
           <soap:address location="<?php echo APP_ROOT."soap/projekt.soap.php";?>"/>
       </wsdl:port>
   </wsdl:service>
</wsdl:definitions>
