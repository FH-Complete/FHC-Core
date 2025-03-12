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
	

<!--		<h3>Test Dominik Schneider</h3>
		<core-messages
			ref="formc"
			endpoint="$fhcApi.factory.messages.person"
			type-id="mitarbeiter_uid"
			id="ma0130"
			messageLayout="twoColumnsTableLeft"
			show-table
			open-mode="modal"
			>
		</core-messages>-->
		
<!--		<h3>Test Person fields</h3>
		<core-messages
			ref="formc"
			endpoint="$fhcApi.factory.messages.person"
			type-id="mitarbeiter_uid"
			id="ma0130"
			messageLayout="twoColumnsTableLeft"
			show-table
			open-mode="modal"
			>
		</core-messages>-->
		
	<template v-if="modelValue.prestudent_id">
		<core-messages
			ref="formc"
			endpoint="$fhcApi.factory.messages.person"
			type-id="prestudent_id"
			:id="modelValue.prestudent_id"
			messageLayout="twoColumnsTableLeft"
			open-mode="modal"
			show-table
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