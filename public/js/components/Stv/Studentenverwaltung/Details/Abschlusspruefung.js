import AbschlusspruefungList from "./Abschlusspruefung/AbschlusspruefungList.js";

export default {
	components: {
		AbschlusspruefungList
	},
	props: {
		modelValue: Object
	},
	data(){
		return {}
	},
	template: `
	<div class="stv-details-abschlusspruefung h-100 d-flex flex-column">
	
		<abschlusspruefung-list ref="finalexam" :student="modelValue"></abschlusspruefung-list>
	</div>`
};