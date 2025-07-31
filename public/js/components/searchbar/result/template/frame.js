import ResultAction from "./action.js";
import ResultActions from "./actions.js";

export default {
	name: 'SearchbarResultTemplateFrame',
	components: {
		ResultAction,
		ResultActions
	},
	emits: [ 'actionexecuted' ],
	props: {
		res: Object,
		actions: Object,
		title: String,
		image: String,
		imageFallback: String
	},
	template: `
	<div class="searchbar-result-template-frame">
		<div class="searchbar_grid">
			<div class="searchbar_icon">
				<result-action
					:res="res"
					:action="actions.defaultaction"
					@actionexecuted="$emit('actionexecuted')"
					class="searchbar-rounded-image"
					>
					<img
						v-if="image"
						:src="image"
						/>
					<div v-else>
						<i :class="imageFallback"></i>
					</div>
				</result-action>
			</div>

			<div class="searchbar_data">
				<result-action
					:res="res"
					:action="actions.defaultaction"
					@actionexecuted="$emit('actionexecuted')"
					class="mb-3"
					>
					<span class="fw-bold">{{ title }}</span>
				</result-action>

				<slot></slot>

				<result-actions
					:res="res"
					:actions="actions.childactions"
					@actionexecuted="$emit('actionexecuted')"
					>
				</result-actions>
			</div>
		</div>
	</div>`
};