import CalendarGrid from '../../Base/Grid.js';
import LabelDay from '../../Base/Label/Day.js';
import LabelDow from '../../Base/Label/Dow.js';
import LabelTime from '../../Base/Label/Time.js';

export default {
	name: "DayView",
	components: {
		CalendarGrid,
		LabelDay,
		LabelDow,
		LabelTime
	},
	inject: {
		locale: "locale",
		timezone: "timezone",
		timeGrid: "timeGrid",
		originalEvents: "events"
	},
	props: {
		day: luxon.DateTime
	},
	data() {
		return {
			chosenEvent: null
		};
	},
	computed: {
		axisMain() {
			return [this.day.startOf('day').toMillis()];
		},
		axisParts() {
			if (this.timeGrid) {
				// create {start, end} array
				return this.timeGrid.map(tu => {
					return {
						start: luxon.Duration.fromISOTime(tu.start).toMillis(),
						end: luxon.Duration.fromISOTime(tu.end).toMillis()
					};
				});
			} else {
				// create 07:00-23:00
				return Array.from({ length: 17 }, (e, i) => luxon.Duration.fromObject({ hours: i + 7 }).toMillis());
			}
		},
		events() {
			return this.originalEvents
				.filter(event => event.start.ts < this.day.plus({ days: 1 }).ts && event.end.ts > this.day.ts)
				.sort((a,b) => a.start.ts-b.start.ts)
				.map(evt => evt.orig);
		},
		currentEvent() {
			if (this.chosenEvent) {
				if (this.events.find(e => e == this.chosenEvent))
					return this.chosenEvent;
			}
			if (this.events)
				return this.events.find(Boolean); // undefined => none found
			return null; // null => loading
		}
	},
	methods: {
		handleClickDefaults(evt) {
			if (evt.detail.source == 'event') {
				this.chosenEvent = evt.detail.value;
			}
		}
	},
	template: `
	<div
		class="fhc-calendar-mode-day-view d-flex h-100"
		@cal-click-default.capture="handleClickDefaults"
	>
		<calendar-grid
			:axis-main="axisMain"
			:axis-parts="axisParts"
			:snap-to-grid="!!timeGrid"
		>
			<template #main-header="{ timestamp }">
				<label-dow
					@cal-click="evt => evt.detail.source = 'day'"
					v-bind="{ timestamp }"
				/>
				<label-day
					v-bind="{ timestamp }"
				/>
			</template>
			<template #part-header="{ part }">
				<label-time v-bind="{ part }" />
			</template>
			<template #event="slot">
				<slot v-bind="slot" mode="day" />
			</template>
		</calendar-grid>
		<div class="w-100">
			<div v-if="currentEvent === null">loading...</div>
			<slot v-else :event="currentEvent" mode="event" />
		</div>
	</div>
	`
}
