import TblMultiStatus from "./Prestudent/MultiStatus.js";

export default {
	components: {
		TblMultiStatus
	},
	props: {
		modelValue: Object,
	},
	template: `
	<div class="stv-details-multistatus h-100">
		<tbl-multi-status :model-value="modelValue"></tbl-multi-status>		
	</div>
	`
}