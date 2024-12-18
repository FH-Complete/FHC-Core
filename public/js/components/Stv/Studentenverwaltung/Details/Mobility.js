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

	<h1>TEST Mob</h1>
	
		<table-mobility ref="tbl_mobility" :prestudent="modelValue"></table-mobility>
	</div>`
};