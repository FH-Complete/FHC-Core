import PersonFunctions from "../../../Funktionen/Funktionen.js";
/*import fhcapifactory from "../../../../apps/api/fhcapifactory.js";
import pv21apifactory from "../../../../../extensions/FHC-Core-Personalverwaltung/js/api/api.js";
Vue.$fhcapi = {...fhcapifactory, ...pv21apifactory};*/

export default {
   components: {
   	PersonFunctions
   },
 props: {
		   modelValue: Object,
	   },
   template: `
   <div class="stv-details-functions h-100 d-flex flex-column">
		<table-functions 
			ref="tbl_functions" 
			:student="modelValue">	
		</table-functions>

	   <person-functions
		   :readonlyMode="false"
		   :personID="modelValue.person_id"
		   :personUID="modelValue.uid"
	   >
	   </person-functions>

      </div>`
};
