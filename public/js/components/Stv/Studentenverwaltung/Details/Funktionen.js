import PersonFunctions from "../../../../../extensions/FHC-Core-Personalverwaltung/js/components/employee/JobFunction.js";
import fhcapifactory from "../../../../apps/api/fhcapifactory.js";
import pv21apifactory from "../../../../../extensions/FHC-Core-Personalverwaltung/js/api/api.js";
Vue.$fhcapi = {...fhcapifactory, ...pv21apifactory};

export default {
	components: {
		PersonFunctions
	},
	props: {
		modelValue: Object,
	},
	template: `
	<div class="stv-details-mobility h-100 d-flex flex-column">
		<table-mobility ref="tbl_functions" :student="modelValue"></table-mobility>

			<person-functions
				:readonlyMode="false"
				:personID="modelValue.person_id"
				:personUID="modelValue.uid"
			>
			</person-functions>

	</div>`
};