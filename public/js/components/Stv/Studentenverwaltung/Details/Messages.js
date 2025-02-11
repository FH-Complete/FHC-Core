import CoreMessages from "../../../Messages/Messages.js";

export default {
	components: {
		CoreMessages
	},
	props: {
		modelValue: Object
	},
	template: `
	<div class="stv-details-messages h-100 pb-3 overflow-hidden">
		<core-messages
			ref="formc"
			endpoint="$fhcApi.factory.messages.person"
			type-id="person_id"
			:id="modelValue.person_id"
			messageLayout="listTableTop"
			show-table
			>
		</core-messages>

	</div>
	`
};