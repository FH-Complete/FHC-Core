export default {
	name:'GridItem',
	props: {
		item: Object,
		active: Boolean
	},
	emits: [
		"mouseDown",
		"mouseUp",
		"startMove",
		"startResize",
		"dragging",
		"endDrag",
		"touchStart",
		"touchEnd"
	],
	data() {
		return {
			dragAction: '',
			dragging: false
		};
	},
	methods: {
		registerDragAction(evt) {
			this.$emit('mouseDown', evt);
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
		tryDragStart(evt) {
			let dragAction = this.dragAction || evt.target.getAttribute('drag-action');
			if (dragAction) {
				this.dragging = true;
				if (dragAction == 'move')
					return this.$emit('startMove', evt, this.item);
				else if (dragAction == 'resize')
					return this.$emit('startResize', evt, this.item);
			}
		},
		touchDragEnd(evt) {
			if (!this.dragging)
				return;
			this.dragging = false;
			this.$emit('touchEnd', evt);
		},
		touchStart(event) {
			this.$emit('touchStart', event); 
			this.registerDragAction(event); 
			this.tryDragStart(event);
		},
		touchMove(event) {
			if (this.dragging) {
				event.preventDefault();
				this.$emit('dragging', event);
			}
		}
		
	},
	template: /* html */`
	<div
		class="drop-grid-item"
		@mousedown="registerDragAction"
		@mouseup="$emit('mouseUp', $event)"
		@touchstart="touchStart"
		@touchend="touchDragEnd"
		@dragstart="tryDragStart"
		@drag="$emit('dragging', $event)"
		@touchmove="touchMove"
		@dragend="$emit('endDrag', $event); dragging = false"
	>
		<slot v-bind="item"></slot>
	</div>`
}
