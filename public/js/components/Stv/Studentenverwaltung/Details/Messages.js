import CoreMessages from "../../../Messages/Messages.js";
//import CoreMessages from "@/Messages/Messages.js";

//TODO(Manu) refactor props

export default {
	components: {
		CoreMessages
	},
	props: {
		modelValue: Object
	},
	template: `
	<div class="stv-details-messages h-100 pb-3 overflow-hidden">
	<template v-if="modelValue.prestudent_id">
		<core-messages
			ref="formc"
			endpoint="$fhcApi.factory.messages.person"
			type-id="prestudent_id"
			:id="modelValue.prestudent_id"
			messageLayout="twoColumnsTableLeft"
			show-table
			open-mode="newTab"
			>
		</core-messages>
	</template>
	<template v-else>
		 <h3><strong>No valid prestudent_id!</strong></h3>
		 <p>{{modelValue.anmerkungen}}</p>
	</template>
	

	</div>
	`
};