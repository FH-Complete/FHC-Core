import CalDnd from '../../../../../directives/Calendar/DragAndDrop.js';
import CalClick from '../../../../../directives/Calendar/Click.js';

export default {
	name: "GridLineEvent",
	directives: {
		CalDnd,
		CalClick
	},
	inject: {
		draggableEvents: "draggableEvents",
		mode: "mode"
	},
	props: {
		event: {
			type: Object,
			required: true,
			validator(value) {
				return (value.start && value.end && value.orig);
			}
		}
	},
	computed: {
		isHeaderOrFooter() {
			return ['header', 'footer'].includes(this.event.orig);
		},
		draggable() {
			return !this.isHeaderOrFooter && this.draggableEvents(this.event.orig, this.mode);
		},
		classes() {
			const classes = [];
			if (this.isHeaderOrFooter) {
				classes.push('event-' + this.event.orig);
			} else {
				if (this.event.startsHere)
					classes.push('event-begin');
				if (this.event.endsHere)
					classes.push('event-end');
			}
			return classes
		}
	},
	template: /* html */`
	<div
		class="fhc-calendar-base-grid-line-event"
		:class="classes"
		style="z-index: 1"
		:draggable="draggable"
		v-cal-dnd:draggable="event"
		v-cal-click:event="isHeaderOrFooter ? event : event.orig"
	>
		<slot :event="isHeaderOrFooter ? event : event.orig">
			{{ event.orig }}
		</slot>
	</div>
	`
}
