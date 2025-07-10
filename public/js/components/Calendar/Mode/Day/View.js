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
		timeGrid: "timeGrid",
		originalEvents: "events"
	},
	props: {
		day: {
			type: luxon.DateTime,
			required: true
		}
	},
	data() {
		return {
			chosenEvent: null
		};
	},
	computed: {
		axisMain() {
			return [this.day.startOf('day')];
		},
		axisParts() {
			if (this.timeGrid) {
				// create {start, end} array
				return this.timeGrid.map(tu => {
					return {
						start: luxon.Duration.fromISOTime(tu.start),
						end: luxon.Duration.fromISOTime(tu.end)
					};
				});
			} else {
				// create 07:00-23:00
				return Array.from({ length: 17 }, (e, i) => luxon.Duration.fromObject({ hours: i + 7 }));
			}
		},
		events() {
			return this.originalEvents
				.filter(event => event.start < this.day.plus({ days: 1 }) && event.end > this.day)
				.sort((a, b) => a.start.ts - b.start.ts)
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
	template: /* html */`
	<div
		class="fhc-calendar-mode-day-view d-flex h-100"
		@cal-click-default.capture="handleClickDefaults"
	>
		<calendar-grid
			:axis-main="axisMain"
			:axis-parts="axisParts"
			:snap-to-grid="!!timeGrid"
			all-day-events
		>
			<template #main-header="{ date }">
				<label-dow
					@cal-click="evt => evt.detail.source = 'day'"
					v-bind="{ date }"
				/>
				<label-day
					v-bind="{ date }"
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
