import AbschlussPruefung from "./Abschlusspruefung/Abschlusspruefung.js";

export default {
	components: {
		AbschlussPruefung
	},
	provide() {
		return {
			config: this.config
		};
	},
	props: {
		modelValue: Object,
		config: Object
	},
	data(){
		return {}
	},
	template: `
	<div class="stv-details-abschlusspruefung h-100 d-flex flex-column">
	
		<abschluss-pruefung ref="finalexam" :student="modelValue"></abschluss-pruefung>
	</div>`
};