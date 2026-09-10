import drop from '../../../directives/drop.js';
import draggable from '../../../directives/draggable.js';

export default {
	directives: {
		drop,
		draggable
	},
	emits: [
		'drop'
	],
	props: {
		node: {
			type: Object,
			required: true
		}
	},
	computed: {
		name() {
			if (Array.isArray(this.node.data.name))
				return this.$p.t(this.node.data.name);

			return this.node.data.name;
		},
		title() {
			if (!this.node.data.title)
				return this.name;

			if (Array.isArray(this.node.data.title))
				return this.$p.t(this.node.data.title);

			return this.node.data.title;
		},
		dropConfig() {
			if (!this.node.data?.droplink)
				return null;

			const allowed = [ ...this.node.data.droplink ];
			const effect = allowed.shift();

			return { effect, allowed };
		},
		dragConfig() {
			if (!this.node.data?.draggable)
				return null;

			let config = [ ...this.node.data.draggable ];
			const effect = config.shift();
			const value = JSON.parse(config.shift());

			return { effect, value };
		}
	},
	template: /* html */`
	<span
		class="treemenu-entry d-flex align-items-center w-100 h-100"
		:title="title"
		v-drop:[dropConfig]="(evt, data) => $emit('drop', { drop: node.data, drag: data })"
		v-draggable:[dragConfig?.effect]="dragConfig?.value"
	>
		{{ name }}
	</span>`
};
