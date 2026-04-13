export default {
	name:'GridItem',
	props: {
		item: Object,
		active: Boolean
	},
	emits: [
		"startMove",
		"startResize",
		"dragging",
		"endDrag"
	],
	data() {
		return {
			dragging: false
		};
	},
	methods: {
		tryDragStart(evt) {
			let dragAction = evt.target.getAttribute('drag-action');
			if (dragAction) {
				if (dragAction == 'move')
					return this.$emit('startMove', evt, this.item);
				else if (dragAction == 'resize')
					return this.$emit('startResize', evt, this.item);
			}
		}
		
	},
	template: /* html */`
	<div
		class="drop-grid-item"
		@dragstart="tryDragStart"
		@drag="$emit('dragging', $event)"
		@dragend="$emit('endDrag', $event)"
	>
		<slot v-bind="item"></slot>
	</div>`
}
