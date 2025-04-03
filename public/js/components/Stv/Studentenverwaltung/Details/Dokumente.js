import ViewDocuments from "./Dokumente/Dokumente.js";

export default {
	components: {
		ViewDocuments
	},
/*	provide() {
		return {
			config: this.config
		};
	},*/
	props: {
		modelValue: Object,
	},
	data(){
		return {}
	},
	template: `
	<div class="stv-details-documents h-100 d-flex flex-column">
		<view-documents ref="vw_documents" :student="modelValue"></view-documents>
	</div>`
};