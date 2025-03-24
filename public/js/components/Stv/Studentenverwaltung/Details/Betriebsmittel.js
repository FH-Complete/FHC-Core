import CoreBetriebsmittel from "../../../Betriebsmittel/Betriebsmittel.js";

import ApiBetriebsmittelPerson from '../../../../api/factory/betriebsmittel/person.js';

export default {
	components: {
		CoreBetriebsmittel
	},
	props: {
		modelValue: Object
	},
	data() {
		return {
			endpoint: ApiBetriebsmittelPerson
		};
	},
	template: `
	<div class="stv-details-betriebsmittel h-100 pb-3">
		<core-betriebsmittel
			:endpoint="endpoint"
			ref="formc"
			type-id="person_id"
			:id="modelValue.person_id"
			:uid="modelValue.uid"
			>
		</core-betriebsmittel>	
	</div>
	`
};

