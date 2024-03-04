import TblStatus from "./Prestudent/Status.js";

export default {
	components: {
		TblStatus
	},
	props: {
		modelValue: Object,
	},
	template: `
	<div class="stv-details-details h-100 pb-3">
			<div class="col-12 pb-3">
			<legend>Status</legend>
			<TblStatus :prestudent_id="modelValue.prestudent_id"></TblStatus>		
		</div>
		</div>
	`
}