import LineEvent from "./Line/Event.js";
import LineBackground from "./Line/Background.js";

/**
 * TODO(chris):
 * Event overflow for Month mode (more-button)
 */

export default {
	name: "GridLine",
	components: {
		LineEvent,
		LineBackground,
	},
	inject: ["axisRow", "shouldCompactEvents", "compactibleEventTypes"],
	props: {
		date: {
			type: luxon.DateTime,
			required: true,
		},
		start: {
			type: luxon.DateTime,
			required: true,
		},
		end: {
			type: luxon.DateTime,
			required: true,
		},
		events: {
			type: Array,
			default: [],
		},
		backgrounds: {
			type: Array,
			default: [],
		},
	},
	computed: {
		formattedEvents() {
			let formattedEvents = this.events.map((event) => {
				event.rows = [1, -1];
				if (event.startsHere) {
					event.rows[0] =
						"t_" + event.start.diff(this.date).toMillis();
				}
				if (event.endsHere) {
					event.rows[1] = "t_" + event.end.diff(this.date).toMillis();
				}

				return event;
			});

			if (this.shouldCompactEvents && this.compactibleEventTypes?.length) {
				formattedEvents =
					this.compactEvents(formattedEvents, this.compactibleEventTypes);
			}

			return formattedEvents;
		},
	},
	methods: {
		compactEvents(events, compactibleEventTypes) {
			let formattedEvents = events
				.filter(
					(event) =>
						!compactibleEventTypes.includes(event.type),
				)
				.map((event) => {
					event.display = "default";
					return event;
				});
			let eventsToBeCompacted = events.filter((event) =>
				compactibleEventTypes.includes(event.type),
			);
			let compactedEvents = [];

			eventsToBeCompacted.forEach((event) => {
				let existingCompactedEvent = compactedEvents.find(
					(compactedEvent) =>
						event.rows[0] === compactedEvent.rows[0] &&
						event.rows[1] === compactedEvent.rows[1],
				);

				if (!existingCompactedEvent) {
					compactedEvents.push({
						events: [
							{
								farbe: event.orig.farbe,
							},
						],
						rows: event.rows,
					});
				} else {
					existingCompactedEvent.events.push({
						farbe: event.orig.farbe,
					});
				}
			});

			compactedEvents.forEach((compactedEvent) => {
				if (compactedEvent.events.length < 4) {
					formattedEvents.push({
						display: "compacted",
						...compactedEvent,
					});
				} else {
					formattedEvents.push({
						display: "compacted",
						events: compactedEvent.events.slice(0, 3),
						rows: compactedEvent.rows,
					});
					formattedEvents.push({
						display: "compactedExtra",
						events: compactedEvent.events.slice(3),
						rows: compactedEvent.rows,
					});
				}
			});

			return formattedEvents;
		},
	},
	template: /* html */ `
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
		<template v-for="(event, i) in formattedEvents" :key="i">
			<line-event
				v-if="!event.display || event.display === 'default'"
				:style="'grid-' + axisRow + ': ' + event.rows.join('/')"
				:event="event"
			>
				<template v-slot="slot">
					<slot name="event" v-bind="slot" />
				</template>
			</line-event>
			<div
				v-else-if="event.display === 'compacted'"
				:style="'grid-' + axisRow + ': ' + event.rows.join('/')"
				class="d-flex flex-row justify-content-center gap-1 align-items-center"
			>
				<span
					v-for="(subEvent, subEventIndex) in event.events"
					:key="subEventIndex"
					:style="subEvent.farbe ? {'background-color': '#' + subEvent.farbe} : {}"
					style="height:10px; width:10px;"
					class="border border-dark rounded-circle"
				></span>
			</div>
			<div
				v-else-if="event.display === 'compactedExtra'"
				:style="'grid-' + axisRow + ': ' + event.rows.join('/')"
				class="w-100 d-flex flex-row justify-content-center"
			>
				{{"+" + event.events.length}}
			</div>
		</template>
		<slot name="dropzone" />
	</div>
	`,
};
