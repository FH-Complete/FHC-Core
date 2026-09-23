import TblJointStudies from "./JointStudies/JointStudies.js";

export default {
	name: 'Tab_JointStudies',
	components: {
		TblJointStudies
	},
	props: {
		modelValue: Object,
	},
	template: `
	<div class="stv-details-jointstudies h-100">
		<tbl-joint-studies :student="modelValue"></tbl-joint-studies>
	</div>
	`
}