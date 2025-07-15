import LineEvent from './Line/Event.js';
import LineBackground from './Line/Background.js';

/**
 * TODO(chris):
 * Event overflow for Month mode (more-button)
 */

export default {
	name: "GridLine",
	components: {
		LineEvent,
		LineBackground
	},
	inject: {
		axisRow: "axisRow"
	},
	props: {
		date: {
			type: luxon.DateTime,
			required: true
		},
		start: {
			type: luxon.DateTime,
			required: true
		},
		end: {
			type: luxon.DateTime,
			required: true
		},
		events: {
			type: Array,
			default: []
		},
		backgrounds: {
			type: Array,
			default: []
		}
	},
	computed: {
		eventsWithRowInfo() {
			const events = [];
			this.events.forEach(event => {
				const rows = [2, -1];
				if (event.startsHere) {
					rows[0] = 't_' + event.start.diff(this.date).toMillis();
				}
				if (event.endsHere) {
					rows[1] = 't_' + event.end.diff(this.date).toMillis();
				}

				events.push({
					...event,
					rows
				});
			});
			return events;
		}
	},
	template: /* html */`
	<div
		class="fhc-calendar-base-grid-line"
		style="position:relative;display:grid;grid-auto-flow:dense"
		:style="'grid-template-' + axisRow + 's:subgrid'"
	>
		<line-background
			v-for="bg in backgrounds"
			:start="start"
			:end="end"
			:background="bg"
		></line-background>
		<line-event
			v-for="(event, i) in eventsWithRowInfo"
			:key="i"
			:style="'grid-' + axisRow + ': ' + event.rows.join('/')"
			:event="event"
		>
			<template v-slot="slot">
				<slot name="event" v-bind="slot" />
			</template>
		</line-event>
		<slot name="dropzone" />
	</div>
	`
}
