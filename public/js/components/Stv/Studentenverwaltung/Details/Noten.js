import NotenZeugnis from './Noten/Zeugnis.js';

export default {
	components: {
		NotenZeugnis
	},
	props: {
		modelValue: Object
	},
	methods: {
		reload() {
			this.$refs.zeugnis.$refs.table.reloadTable();
		}
	},
	template: `
	<div class="stv-details-noten h-100 d-flex flex-column">
		<noten-zeugnis ref="zeugnis" :student="modelValue"></noten-zeugnis>
	</div>`
};