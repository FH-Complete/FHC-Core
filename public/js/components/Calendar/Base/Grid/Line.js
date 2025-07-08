import LineEvent from './Line/Event.js';
import LineBackground from './Line/Background.js';

/**
 * TODO(chris):
 * Event overflow for Month view (more-button)
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
		stops() {
			const stops = this.events.reduce((stops, event) => {
				if (event.startsHere) {
					if (stops.indexOf(event.start.ts) < 0)
						stops.push(event.start.ts);
				}
				if (event.endsHere) {
					if (stops.indexOf(event.end.ts) < 0)
						stops.push(event.end.ts);
				}
				return stops;
			}, []).sort((a,b) => a-b);

			if (stops[0] == this.start)
				stops.shift();
			if (stops[stops.length-1] == this.end)
				stops.pop();

			return stops;
		},
		eventGrid() {
			const perc = (this.end.ts - this.start.ts) / 100;

			let last = this.start.ts;
			const grid = this.stops.map(stop => {
				let length = stop - last;
				last = stop;
				return length / perc + '%';
			});

			/*if (grid.filter((e, i, a) => a.indexOf(e) == i).length == 1) {
				return Array.from({length: grid.length + 1}, () => '1fr').join(' ');
			}*/
			//return grid.join(' ') + ' ' + (this.end - last) / perc + '%';
			
			return grid.join(' ') + ' 1fr';
		},
		eventsWithRowInfo() {
			const events = [];
			this.events.forEach(event => {
				const rows = [1, -1];
				if (event.startsHere) {
					rows[0] = this.stops.indexOf(event.start.ts) + 2;
				}
				if (event.endsHere) {
					rows[1] = this.stops.indexOf(event.end.ts) + 2;
					if (rows[1] === 1)
						rows[1] = -1;
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
		:style="'grid-template-' + axisRow + 's:' + eventGrid"
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
