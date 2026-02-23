import TblMultiStatus from "./Prestudent/MultiStatus.js";

export default {
	name: "TabStatus",
	components: {
		TblMultiStatus
	},
	props: {
		modelValue: Object,
		config: {
			type: Object,
			default: {}
		}
	},
	template: `
	<div class="stv-details-multistatus h-100">
		<tbl-multi-status :model-value="modelValue" :config="config"></tbl-multi-status>
	</div>
	`
}