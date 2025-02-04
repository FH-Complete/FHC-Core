import GruppenList from './Gruppen/Gruppen.js';

export default {
	components: {
		GruppenList
	},
	props: {
		modelValue: Object
	},
	methods: {
		reload() {
			this.$refs.gruppen.$refs.table.reloadTable();
		}
	},
	template: `
	<div class="stv-details-gruppen h-100 d-flex flex-column">
		<gruppen-list ref="gruppen" :student="modelValue"></gruppen-list>
	</div>`
};