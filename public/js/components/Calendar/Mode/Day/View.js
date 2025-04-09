import CalendarGrid from '../../Base/Grid.js';
import LabelDay from '../../Base/Label/Day.js';
import LabelDow from '../../Base/Label/Dow.js';
import LabelTime from '../../Base/Label/Time.js';

import CalendarDate from '../../../../helpers/Calendar/Date.js';

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
		timeGrid: "timeGrid",
		originalEvents: "events"
	},
	props: {
		day: Number
	},
	data() {
		return {
			chosenEvent: null
		};
	},
	computed: {
		axisMain() {
			return [this.day];
		},
		axisParts() {
			const referenceDate = new Date("2000-01-01 00:00:00");
			
			if (this.timeGrid) {
				// create {start, end} array
				return this.timeGrid.map(tu => {
					const startDate = new Date("2000-01-01 " + tu.start);
					const endDate = new Date("2000-01-01 " + tu.end);
					return {
						start: startDate - referenceDate,
						end: endDate - referenceDate
					};
				});
			} else {
				// create 07:00-23:00
				return [...Array(17).keys()].map(i => {
					const time = ('0' + (i + 7)).slice(-2) + ':00:00';
					const date = new Date("2000-01-01 " + time);
					return date - referenceDate;
				});
			}
		},
		events() {
			return this.originalEvents.filter(event => event.start < this.day + CalendarDate.msPerDay && event.end > this.day).sort((a,b) => a.start-b.start).map(evt => evt.orig);
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
