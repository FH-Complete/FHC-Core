import AnrechnungenList from './Anrechnungen/Anrechnungen.js';

export default {
	name: "TabExemptions",
	components: {
		AnrechnungenList
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
	methods: {
		reload() {
			this.$refs.anrechnungen.$refs.table.reloadTable();
		}
	},
	template: `
	<div class="stv-details-anrechnungen h-100 d-flex flex-column">
		<anrechnungen-list ref="anrechnungen" :student="modelValue"></anrechnungen-list>
	</div>`
};