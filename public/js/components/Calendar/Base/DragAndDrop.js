import CalendarEvent from '../../../helpers/Calendar/Event.js';
import DragAndDrop from '../../../helpers/DragAndDrop.js';

import CalDnd from '../../../directives/Calendar/DragAndDrop.js';

/**
 * TODO(chris): this needs serious rework!
 */

export default {
	name: "CalendarDragAndDrop",
	directives: {
		CalDnd
	},
	provide() {
		return {
			events: Vue.computed(() => this.correctedEvents),
			backgrounds: Vue.computed(() => this.backgrounds),
			dropAllowed: Vue.computed(() => this.dragging && this.dropAllowed)
		};
	},
	inject: {
		mode: "mode",
		dropableEvents: "dropableEvents"
	},
	props: {
		events: Array,
		backgrounds: Array
	},
	emits: [
		"drop"
	],
	data() {
		return {
			dragging: false,
			allowed: false,
			draggedInternalEvent: null,
			draggedExternalEvent: null,
			targetTimestamp: 0,
			targetGridEnds: null,
			dropAllowed: false,

			shadowPreview: false // TODO(chris): IMPLEMENT! (use background instead of event as preview)
		};
	},
	computed: {
		correctedEvents() {
			if (this.dragging) {
				if (this.draggedInternalEvent) {
					const index = this.events.findIndex(e => e.id == this.draggedInternalEvent.id);
					if (this.previewEvent && !this.shadowPreview)
						return this.events.toSpliced(index, 1, this.previewEvent);
					else
						return this.events.toSpliced(index, 1);
				}
				if (this.previewEvent && !this.shadowPreview)
					return [...this.events, this.previewEvent];
			}

			return this.events;
		},
		correctedBackgrounds() {
			if (this.dragging) {
				if (this.shadowPreview) {
					// TODO(chris): how to get the length
					return [...this.backgrounds, {
						start: new Date(this.targetTimestamp),
						class: 'shadow-preview'
					}];
				}
			}

			return this.backgrounds;
		},
		previewEvent() {
			if (!this.dragging || !this.dropAllowed)
				return null;
			if (!this.targetTimestamp)
				return null;

			const event = this.draggedInternalEvent || this.draggedExternalEvent;

			if (!event)
				return null;
			
			// TODO(chris): calculate length correctly from orig
			let length = event.end - event.start;
			if (this.targetGridEnds)
				length = this.targetGridEnds.find(end => end >= this.targetTimestamp + length) - this.targetTimestamp;
			
			return {
				orig: event.orig,
				start: this.targetTimestamp,
				end: this.targetTimestamp + length
			};
		}
	},
	methods: {
		onDragstart(evt) {
			let test = DragAndDrop.setTransferData(evt.detail.originalEvent, evt.detail.item.orig);
			this.draggedInternalEvent = evt.detail.item;
		},
		onDragend(evt) {
			this.draggedInternalEvent = null;
			this.dragging = false;
		},
		onDragenter(evt) {
			this.dragging = true;

			if (!this.draggedInternalEvent) {
				const event = DragAndDrop.getValidTransferData(evt.detail.originalEvent);
				this.draggedExternalEvent = event ? CalendarEvent.smartConvert(event) : null;
				this.dropAllowed = this.dropableEvents(event, this.mode);
			} else {
				this.dropAllowed = this.dropableEvents(this.draggedInternalEvent, this.mode);
			}
		},
		onDragleave(evt) {
			this.dragging = false;
		},
		onDragchange(evt) {
			this.targetTimestamp = evt.detail.timestamp;
			
			this.targetGridEnds = evt.detail.ends || null;
		},
		onDrop(evt) {
			if (!this.dragging || !this.dropAllowed)
				return;

			this.$emit('drop', evt, this.previewEvent.start, this.previewEvent.end);
			this.dropAllowed = false;
			this.dragging = false;
		}
	},
	template: `
	<div
		class="fhc-calendar-base-draganddrop"
		@calendar-dragstart="onDragstart"
		@calendar-dragend="onDragend"
		v-cal-dnd:dropcage
		@calendar-dragenter="onDragenter"
		@calendar-dragleave="onDragleave"
		@calendar-dragchange="onDragchange"
		@drop="onDrop"
	>
		<slot />
	</div>
	`
}
