import TableMobility from "./Mobility/Mobility.js";

export default {
	components: {
		TableMobility
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
	<div class="stv-details-mobility h-100 d-flex flex-column">	
		<table-mobility ref="tbl_mobility" :student="modelValue"></table-mobility>
	</div>`
};