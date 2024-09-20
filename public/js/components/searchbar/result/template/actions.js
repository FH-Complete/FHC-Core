import ResultAction from "./action.js";

export default {
	components: {
		ResultAction
	},
	emits: [ 'actionexecuted' ],
	props: {
		res: Object,
		actions: Array
	},
	template: `
	<div v-if="actions.length" class="searchbar-actions">
		<result-action
			v-for="(action, index) in actions"
			:key="action.label"
			:res="res"
			:action="action"
			class="btn btn-primary btn-sm"
			@actionexecuted="$emit('actionexecuted')"
			>
			<i v-if="action.icon" :class="action.icon"></i>
			<span class="p-2">{{ action.label }}</span>
		</result-action>
	</div>`
};