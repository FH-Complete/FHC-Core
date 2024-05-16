import TblMultiStatus from "./Prestudent/MultiStatus.js";

export default {
	components: {
		TblMultiStatus
	},
	props: {
		modelValue: Object,
	},
	template: `
	<div class="stv-details-multistatus h-100 pb-3">
		<div class="col-12 pb-3">
			<legend>Status</legend>
			<tbl-multi-status :model-value="modelValue"></tbl-multi-status>		
		</div>
	</div>
	`
}