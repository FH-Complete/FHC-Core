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
		locale: "locale",
		axisRow: "axisRow"
	},
	props: {
		start: Number,
		end: Number,
		timestamp: Number,
		events: Array,
		backgrounds: Array
	},
	computed: {
		stops() {
			const stops = this.events.reduce((stops, event) => {
				if (event.startsHere) {
					if (stops.indexOf(event.start) < 0)
						stops.push(event.start);
				}
				if (event.endsHere) {
					if (stops.indexOf(event.end) < 0)
						stops.push(event.end);
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
			const perc = (this.end - this.start) / 100;

			let last = this.start;
			const grid = this.stops.map(stop => {
				let length = stop - last;
				last = stop;
				return length / perc + '%';
			});

			if (grid.filter((e, i, a) => a.indexOf(e) == i).length == 1) {
				return Array.from({length: grid.length + 1}, () => '1fr').join(' ');
			}

			return grid.join(' ') + ' 1fr';
		},
		eventsWithRowInfo() {
			const events = [];
			this.events.forEach(event => {
				const rows = [1, -1];
				if (event.startsHere) {
					rows[0] = this.stops.indexOf(event.start) + 2;
				}
				if (event.endsHere) {
					rows[1] = this.stops.indexOf(event.end) + 2;
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
	template: `
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
