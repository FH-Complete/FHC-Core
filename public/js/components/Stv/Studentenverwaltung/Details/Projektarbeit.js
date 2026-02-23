import Projektarbeit from './Projektarbeit/Projektarbeit.js';

export default {
	name: "TabProjektarbeit",
	components: {
		Projektarbeit
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
	<div class="stv-details-projektarbeit h-100 d-flex flex-column">
		<projektarbeit ref="projektarbeit" :student="modelValue"></projektarbeit>
	</div>`
};