import NotizComponent from "../../../Notiz/NotizComponent.js";

export default {
	components: {
		NotizComponent
	},
	props: {
		modelValue: Object
	},
	data(){
		return {
			showErweitert: true, //show details verfasser, bearbeiter, von, bis, erledigt
			showDocument: true, //show upload documents
		};
	},
	template: `
	<div class="stv-details-details h-100 pb-3">
		<NotizComponent
			ref="formc"
			typeId="person_id"
			:id="modelValue.person_id"
			:showErweitert=true
			:showDocument=true
			>		
		</NotizComponent>
	</div>
	`
};
