import CoreMessages from "../../../Messages/Messages.js";
//import CoreMessages from "@/Messages/Messages.js";

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
			type-id="uid"
			:id="modelValue.uid"
			messageLayout="twoColumnsTableLeft"
			show-table
			show-new
			open-mode="showDiv"
			>
		</core-messages>

	</div>
	`
};