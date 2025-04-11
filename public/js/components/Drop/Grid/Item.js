export default {
	name:'GridItem',
	components: {
	},
	inject: {
	},
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
		"dropDrag",
		"item",
		"touchEvent"
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
		tryDragStart(evt, item) {
			let dragAction = this.dragAction || evt.target.getAttribute('drag-action');
			if (dragAction) {
				this.dragging = true;
				if (dragAction == 'move')
					return this.$emit('startMove', evt, item);
				else if (dragAction == 'resize')
					return this.$emit('startResize', evt, item);
			}
			//evt.preventDefault();
		},
		touchDragEnd(evt) {
			if (!this.dragging)
				//return evt.preventDefault();
                                return;
			this.dragging = false;
			this.$emit('dropDrag', evt);
		},
		touchStart(event){
			this.$emit('touchEvent', event); 
			this.registerDragAction(event); 
			this.tryDragStart(event, this.item);
		}
	},
	template: `
	<div class="drop-grid-item"
		@mousedown="registerDragAction"
		@mouseup="$emit('mouseUp', $event)"
		@touchstart.prevent="touchStart"
		@touchend="touchDragEnd"
		@dragstart="tryDragStart($event, item)"
		@drag="$emit('dragging',$event)"
		@touchmove="$emit('dragging',$event)"
		@dragend="$emit('endDrag', $event)"
		:draggable="active && !item.placeholder && dragAction != ''">
		<slot v-bind="item"></slot>
	</div>`
}
