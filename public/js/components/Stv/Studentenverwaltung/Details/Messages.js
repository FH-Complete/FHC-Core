import CoreMessages from "../../../Messages/Messages.js";
import ApiMessages from "../../../../api/factory/messages/messages.js";

export default {
	name: "TabMessages",
	components: {
		CoreMessages
	},
	props: {
		modelValue: Object
	},
	data(){
		return {
			endpoint: ApiMessages
		};
	},
	computed: {
		prestudent_ids() {
			if (this.modelValue.prestudent_id)
			{
				return [this.modelValue.prestudent_id];
			}
			return this.modelValue.map(e => e.prestudent_id);
		},
		person_ids() {
			if (this.modelValue.person_id)
			{
				return [this.modelValue.person_id];
			}
			return this.modelValue.map(e => e.person_id);
		},
	},
	template: `
	<div class="stv-details-messages h-100 pb-3 overflow-hidden">
		<template v-if="prestudent_ids">
			<core-messages
				ref="formc"
				:endpoint="endpoint"
				type-id="prestudent_id"
				:id="prestudent_ids"
				messageLayout="twoColumnsTableLeft"
				open-mode="modal"
				show-table
				>
			</core-messages>
		</template>
		<template v-else >
			<core-messages
				ref="formc"
				:endpoint="endpoint"
				type-id="person_id"
				:id="person_ids"
				messageLayout="twoColumnsTableLeft"
				open-mode="modal"
				show-table
				>
			</core-messages>
		</template>
	</div>
	`
};