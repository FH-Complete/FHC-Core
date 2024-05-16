import CoreBetriebsmittel from "../../../Betriebsmittel/Betriebsmittel.js";

export default {
	components: {
		CoreBetriebsmittel
	},
	props: {
		modelValue: Object
	},
	template: `
	<div class="stv-details-betriebsmittel h-100 pb-3">
		<core-betriebsmittel
			ref="formc"
			:person_id="modelValue.person_id"
			:uid="modelValue.uid"
			>
		</core-betriebsmittel>	
	</div>
	`
};

