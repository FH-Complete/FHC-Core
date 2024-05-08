export default {
	components: {
	},
	inject: {
	},
	props: {
		item: Object,
		active: Boolean
	},
	emits: [
		"startMove",
		"startResize",
		"endDrag",
		"dropDrag"
	],
	data() {
		return {
			dragAction: '',
			dragging: false
		}
	},
	computed: {
	},
	methods: {
		registerDragAction(evt) {
			if (evt.target.hasAttribute('drag-action')) {
				this.dragAction = evt.target.getAttribute('drag-action');
			} else {
				let parent = evt.target.closest('[drag-action]');
				if (parent) {
					this.dragAction = parent.getAttribute('drag-action');
				} else {
					this.dragAction = '';
				}
			}
		},
		tryDragStart(evt, item) {
			let dragAction = this.dragAction || evt.target.getAttribute('drag-action');
			if (dragAction) {
				this.dragging = true;
				if (dragAction == 'move')
					return this.$emit('startMove', evt, item);
				else if (dragAction == 'resize')
					return this.$emit('startResize', evt, item);
			}
			evt.preventDefault();
		},
		touchDragEnd(evt) {
			if (!this.dragging)
				return evt.preventDefault();
			this.dragging = false;
			this.$emit('dropDrag', evt);
		},
		test(evt) {
			let dragAction = this.dragAction || evt.target.getAttribute('drag-action');
			if (dragAction) {
				this.dragging = true;
			}
		}
	},
	template: `
	<div class="drop-grid-item"
		@mousedown="registerDragAction"
		@touchstart="tryDragStart($event, item)"
		@touchend="touchDragEnd"
		@dragstart="tryDragStart($event, item)"
		@dragend="$emit('endDrag', $event)"
		:draggable="active">
		<slot v-bind="item"></slot>
	</div>`
}
