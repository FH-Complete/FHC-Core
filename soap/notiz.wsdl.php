<?php 
require_once('../config/vilesci.config.inc.php');
header("Content-type: text/plain");
echo "<?xml version='1.0' encoding='utf-8' ?>";
?>

<wsdl:definitions name="Notiz" 
targetNamespace="http://www.technikum-wien.at/soap/" 
xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" 
xmlns:tns="http://www.technikum-wien.at/soap/" 
xmlns:xsd="http://www.w3.org/2001/XMLSchema"    
xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/">

	<wsdl:message name="saveNotizRequest">
		<wsdl:part name="username" type="xsd:string" minOccurs="0"></wsdl:part>
		<wsdl:part name="passwort" type="xsd:string" minOccurs="0"></wsdl:part>
		<wsdl:part name="notiz" type="tns:notiz"></wsdl:part>
    </wsdl:message>
	
	<xsd:complexType name="notiz">
	  <xsd:all>	
			<wsdl:part name="notiz_id" type="xsd:string"></wsdl:part>
			<wsdl:part name="titel" type="xsd:string"></wsdl:part>
			<wsdl:part name="text" type="xsd:string"></wsdl:part>
			<wsdl:part name="verfasser_uid" type="xsd:string"></wsdl:part>
			<wsdl:part name="bearbeiter_uid" type="xsd:string"></wsdl:part>
			<wsdl:part name="start" type="xsd:string"></wsdl:part>
			<wsdl:part name="ende" type="xsd:string"></wsdl:part>
			<wsdl:part name="erledigt" type="xsd:boolean"></wsdl:part>
			<wsdl:part name="projekt_kurzbz" type="xsd:string"></wsdl:part>
			<wsdl:part name="projektphase_id" type="xsd:string"></wsdl:part>
			<wsdl:part name="projekttask_id" type="xsd:string"></wsdl:part>
			<wsdl:part name="uid" type="xsd:string"></wsdl:part>
			<wsdl:part name="person_id" type="xsd:string"></wsdl:part>
			<wsdl:part name="prestudent_id" type="xsd:string"></wsdl:part>
			<wsdl:part name="bestellung_id" type="xsd:string"></wsdl:part>
			<wsdl:part name="lehreinheit_id" type="xsd:string"></wsdl:part>
			</xsd:all>
	 </xsd:complexType>
	 
 	<wsdl:message name="saveNotizResponse">
       <wsdl:part name="message" type="xsd:string"></wsdl:part>
  	</wsdl:message>
  	<wsdl:message name="deleteNotizRequest">
		<wsdl:part name="username" type="xsd:string" minOccurs="0"></wsdl:part>
		<wsdl:part name="passwort" type="xsd:string" minOccurs="0"></wsdl:part>  	
  		<wsdl:part name="notiz_id" type="xsd:string"></wsdl:part>
  	</wsdl:message>
    <wsdl:message name="deleteNotizResponse">
  		<wsdl:part name="message" type="xsd:string"></wsdl:part>
  	</wsdl:message>
    <wsdl:message name="deleteDokumentRequest">
		<wsdl:part name="username" type="xsd:string" minOccurs="0"></wsdl:part>
		<wsdl:part name="passwort" type="xsd:string" minOccurs="0"></wsdl:part>  	
  		<wsdl:part name="dms_id" type="xsd:string"></wsdl:part>
  	</wsdl:message>
    <wsdl:message name="deleteDokumentResponse">
  		<wsdl:part name="message" type="xsd:string"></wsdl:part>
  	</wsdl:message>
  	<wsdl:message name="setErledigtRequest">
  		<wsdl:part name="notiz_id" type="xsd:string"></wsdl:part>
  		<wsdl:part name="erledigt" type="xsd:boolean"></wsdl:part>
  	</wsdl:message>
    <wsdl:message name="setErledigtResponse">
  		<wsdl:part name="message" type="xsd:string"></wsdl:part>
  	</wsdl:message>
 
 <wsdl:portType name="ConfigPortType" >
       <wsdl:operation name="saveNotiz">
           <wsdl:input message="tns:saveNotizRequest"></wsdl:input>
           <wsdl:output message="tns:saveNotizResponse"></wsdl:output>        
       </wsdl:operation>
       <wsdl:operation name="deleteNotiz">
           <wsdl:input message="tns:deleteNotizRequest"></wsdl:input>
           <wsdl:output message="tns:deleteNotizResponse"></wsdl:output>        
       </wsdl:operation>
       <wsdl:operation name="deleteDokument">
           <wsdl:input message="tns:deleteDokumentRequest"></wsdl:input>
           <wsdl:output message="tns:deleteDokumentResponse"></wsdl:output>        
       </wsdl:operation>
       <wsdl:operation name="setErledigt">
           <wsdl:input message="tns:setErledigtRequest"></wsdl:input>
           <wsdl:output message="tns:setErledigtResponse"></wsdl:output>        
       </wsdl:operation>
   </wsdl:portType>

   <wsdl:binding name="ConfigBinding" type="tns:ConfigPortType">
       <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http" />
       <wsdl:operation name="saveNotiz">
           <soap:operation soapAction="<?php echo APP_ROOT."soap/saveNotiz";?>" />
           <wsdl:input> 
               <soap:body use="encoded" namespace="http://www.technikum-wien.at/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
           </wsdl:input>
           <wsdl:output>
               <soap:body use="encoded" namespace="http://www.technikum-wien.at/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
           </wsdl:output>
       </wsdl:operation> 
       <wsdl:operation name="deleteNotiz">
           <soap:operation soapAction="<?php echo APP_ROOT."soap/deleteNotiz";?>" />
           <wsdl:input> 
               <soap:body use="encoded" namespace="http://www.technikum-wien.at/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
           </wsdl:input>
           <wsdl:output>
               <soap:body use="encoded" namespace="http://www.technikum-wien.at/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
           </wsdl:output>
       </wsdl:operation>
       <wsdl:operation name="deleteDokument">
           <soap:operation soapAction="<?php echo APP_ROOT."soap/deleteDokument";?>" />
           <wsdl:input> 
               <soap:body use="encoded" namespace="http://www.technikum-wien.at/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
           </wsdl:input>
           <wsdl:output>
               <soap:body use="encoded" namespace="http://www.technikum-wien.at/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
           </wsdl:output>
       </wsdl:operation>
       <wsdl:operation name="setErledigt">
           <soap:operation soapAction="<?php echo APP_ROOT."soap/setErledigt";?>" />
           <wsdl:input> 
               <soap:body use="encoded" namespace="http://www.technikum-wien.at/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
           </wsdl:input>
           <wsdl:output>
               <soap:body use="encoded" namespace="http://www.technikum-wien.at/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
           </wsdl:output>
       </wsdl:operation>  
   </wsdl:binding>  
 
   <wsdl:service name="Notiz">
       <wsdl:port name="ConfigWebservicePort" binding="tns:ConfigBinding">
           <soap:address location="<?php echo APP_ROOT."soap/notiz.soap.php";?>"/>
       </wsdl:port>
   </wsdl:service>
</wsdl:definitions>
