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
		<wsdl:part name="user" type="xsd:string"></wsdl:part>
    </wsdl:message>

 	<wsdl:message name="SaveProjekttaskResponse">
       <wsdl:part name="message" type="xsd:string"></wsdl:part>
  	</wsdl:message>
  	
  	<wsdl:message name="DeleteProjekttaskRequest">
  		<wsdl:part name="projekttask_id" type="xsd:string"></wsdl:part>
  	</wsdl:message>
    <wsdl:message name="DeleteProjekttaskResponse">
  		<wsdl:part name="message" type="xsd:string"></wsdl:part>
  	</wsdl:message>
  	
  	<wsdl:message name="SaveMantisRequest">
  		<wsdl:part name="projekttask_id" type="xsd:string"></wsdl:part>
		<wsdl:part name="mantis_id" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_summary" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_description" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_view_state_id" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_view_state_name" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_last_updated" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_project_id" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_project_name" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_category" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_priority_id" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_priority_name" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_severity_id" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_severity_name" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_status_id" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_status_name" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_reporter_name" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_reporter_real_name" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_reporter_email" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_reproducibility_id" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_reproducibility_name" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_date_submitted" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_sponsorship_total" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_projection_id" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_projection_name" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_eta_id" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_eta_name" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_resolution_id" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_resolution_name" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_due_date" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_steps_to_reproduce" type="xsd:string"></wsdl:part>
		<wsdl:part name="issue_additional_information" type="xsd:string"></wsdl:part>
    </wsdl:message>
    
	<wsdl:message name="SaveMantisResponse">
       <wsdl:part name="message" type="xsd:string"></wsdl:part>
  	</wsdl:message>
  	
	<wsdl:portType name="ConfigPortType" >
       <wsdl:operation name="saveProjekttask">
           <wsdl:input message="tns:SaveProjekttaskRequest"></wsdl:input>
           <wsdl:output message="tns:SaveProjekttaskResponse"></wsdl:output>        
       </wsdl:operation>
       <wsdl:operation name="deleteProjekttask">
           <wsdl:input message="tns:DeleteProjekttaskRequest"></wsdl:input>
           <wsdl:output message="tns:DeleteProjekttaskResponse"></wsdl:output>        
       </wsdl:operation>
       <wsdl:operation name="saveMantis">
           <wsdl:input message="tns:SaveMantisRequest"></wsdl:input>
           <wsdl:output message="tns:SaveMantisResponse"></wsdl:output>        
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
       <wsdl:operation name="deleteProjekttask">
           <soap:operation soapAction="<?php echo APP_ROOT."soap/deleteProjekttask";?>" />
           <wsdl:input> 
               <soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
           </wsdl:input>
           <wsdl:output>
               <soap:body use="encoded" namespace="http://localhost/soap/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
           </wsdl:output>
       </wsdl:operation> 
       <wsdl:operation name="saveMantis">
           <soap:operation soapAction="<?php echo APP_ROOT."soap/saveMantis";?>" />
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
