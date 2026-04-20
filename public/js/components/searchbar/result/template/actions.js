import ResultAction from "./action.js";

export default {
	name: 'SearchbarResultTemplateActions',
	components: {
		ResultAction
	},
	emits: [ 'actionexecuted' ],
	props: {
		res: Object,
		actions: Array
	},
	methods: {
		renderif: function(action) {
			if(action?.renderif === undefined) {
				return true;
			}

			return action.renderif(this.res);
		}
	},
	template: `
	<div v-if="actions.length" class="searchbar-result-template-actions">
		<template v-for="(action, index) in actions" :key="action.label">
		<result-action
			 v-if="this.renderif(action)"
			:res="res"
			:action="action"
			class="btn btn-primary btn-sm"
			@actionexecuted="$emit('actionexecuted')"
			>
			<i v-if="action.icon" :class="action.icon"></i>
			<span class="p-2">{{ action.label }}</span>
		</result-action>
		</template>
	</div>`
};