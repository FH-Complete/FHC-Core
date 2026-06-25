import drop from '../../../directives/drop.js';

export default {
	directives: {
		drop
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
		}
	},
	template: /* html */`
	<span
		class="treemenu-entry d-flex align-items-center w-100 h-100"
		:title="title"
		v-drop:[dropConfig]="(evt, data) => $emit('drop', { drop: node.data, drag: data })"
	>
		{{ name }}
	</span>`
};
