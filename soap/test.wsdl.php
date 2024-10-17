<?php 
require_once('../config/vilesci.config.inc.php');
header("Content-type: text/plain");
echo "<?xml version='1.0' encoding='utf-8' ?>";
?>

<wsdl:definitions name="Test" 
targetNamespace="http://www.technikum-wien.at/soap/" 
xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" 
xmlns:tns="http://www.technikum-wien.at/soap/" 
xmlns:xsd="http://www.w3.org/2001/XMLSchema"    
xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/">

	<wsdl:message name="myTestRequest">
		<wsdl:part name="foo" type="xsd:string" minOccurs="0"></wsdl:part>
    </wsdl:message>
	
	 
 	<wsdl:message name="myTestResponse">
       <wsdl:part name="message" type="xsd:string"></wsdl:part>
  	</wsdl:message>
   
 <wsdl:portType name="ConfigPortType" >
       <wsdl:operation name="myTest">
           <wsdl:input message="tns:myTestRequest"></wsdl:input>
           <wsdl:output message="tns:myTestResponse"></wsdl:output>        
       </wsdl:operation>
   </wsdl:portType>

   <wsdl:binding name="ConfigBinding" type="tns:ConfigPortType">
       <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http" />
       <wsdl:operation name="myTest">
           <soap:operation soapAction="<?php echo APP_ROOT."soap/myTest";?>" />
           <wsdl:input> 
               <soap:body use="literal"/>
           </wsdl:input>
           <wsdl:output>
               <soap:body use="literal"/>
           </wsdl:output>
       </wsdl:operation> 
   </wsdl:binding>  
 
   <wsdl:service name="Test">
       <wsdl:port name="ConfigWebservicePort" binding="tns:ConfigBinding">
           <soap:address location="<?php echo APP_ROOT."soap/test.soap.php";?>"/>
       </wsdl:port>
   </wsdl:service>
</wsdl:definitions>
