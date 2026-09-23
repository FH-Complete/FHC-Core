import AdmissionDates from "./Aufnahmetermine/Aufnahmetermine.js";
import HeaderPlacement from "./Aufnahmetermine/HeaderReihungstest.js";

export default {
	name: "TabAdmissionDates",
	components: {
		AdmissionDates,
		HeaderPlacement
	},
	provide() {
		return {
			config: this.config
		};
	},
	props: {
		modelValue: Object,
	},
	data(){
		return {}
	},
	template: `
	<div class="stv-details-mobility h-30 d-flex flex-column">
		<header-placement :student="modelValue"></header-placement>
		<admission-dates ref="tbl_admission_dates" :student="modelValue"></admission-dates>
	</div>`
};