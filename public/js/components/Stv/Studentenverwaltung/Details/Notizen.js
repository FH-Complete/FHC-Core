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
/*			showErweitert: true, //show details verfasser, bearbeiter, von, bis, erledigt
			showDocument: true, //show upload documents
			showTinyMCE: true
 */
		};
	},
	template: `
	<div class="stv-details-details h-100 pb-3">
		<h3>Notizen</h3>
		<NotizComponent
			ref="formc"
			typeId="person_id"
			:id="modelValue.person_id"
			:showErweitert=true
			:showDocument=true
			:showTinyMCE=true
			>
		</NotizComponent>
		
		<br><br>
		<h3>Test prestudentId</h3>
		<NotizComponent
			ref="formc"
			typeId="prestudent_id"
			:id="modelValue.prestudent_id"
			:showErweitert=false
			:showDocument=true
			>
		</NotizComponent>
		
	</div>
	`
};
