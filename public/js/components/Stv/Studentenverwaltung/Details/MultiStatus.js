import TblMultiStatus from "./Prestudent/MultiStatus.js";

export default {
	components: {
		TblMultiStatus
	},
	props: {
		modelValue: Object,
	},
	template: `

		<div class="stv-details-details h-100 pb-3">
			<div class="col-12 pb-3">
			<legend>MultiStatus</legend>
			<TblMultiStatus :modelValue="modelValue"></TblMultiStatus>		
		</div>
	</div>
	`
}