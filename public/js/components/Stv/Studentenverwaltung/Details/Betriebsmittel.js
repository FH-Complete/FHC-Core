import Betriebsmittel from "../../../Betriebsmittel/Betriebsmittel.js";

export default {
	components: {
		Betriebsmittel
	},
	props: {
		modelValue: Object
	},
	template: `
	<div class="stv-details-details h-100 pb-3">
		<h3>Betriebsmittel</h3>
		<Betriebsmittel
			ref="formc"
			:person_id="modelValue.person_id"
			:uid="modelValue.uid"
			>
		</Betriebsmittel>	
	</div>
	`
};

