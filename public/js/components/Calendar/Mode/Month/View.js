import CalendarGrid from '../../Base/Grid.js';
import LabelWeek from '../../Base/Label/Week.js';
import LabelDow from '../../Base/Label/Dow.js';
import LabelDay from '../../Base/Label/Day.js';

import CalendarDate from '../../../../helpers/Calendar/Date.js';

export default {
	name: "MonthView",
	components: {
		CalendarGrid,
		LabelWeek,
		LabelDow,
		LabelDay
	},
	provide() {
		return {
			// NOTE(chris): snap events to day
			events: Vue.computed(() => {
				//const events = [];
				const events = this.events.map(event => {
					const start = Math.floor(event.start / CalendarDate.msPerDay) * CalendarDate.msPerDay;
					const end = Math.floor(event.end / CalendarDate.msPerDay) * CalendarDate.msPerDay + CalendarDate.msPerDay;
					return {
						...event,
						start,
						end
					};
				});
				for (var w = 5; w > -1; w--) {
					for (var d = 6; d > -1; d--) {
						const start = this.axisMain[w] + this.axisParts[d];
						events.unshift({
							start: start,
							end: start + CalendarDate.msPerDay,
							orig: 'header'
						});
					}
				}
				return events;
			})
		};
	},
	inject: {
		locale: "locale",
		events: "events"
	},
	props: {
		year: Number,
		month: Number
	},
	computed: {
		axisMain() {
			const start = CalendarDate.UTC(CalendarDate.getFirstDayOfWeek(new Date(this.year, this.month, 1), this.locale), true);
			return Array.from({length: 6}, (e, i) => start + i * 7 * CalendarDate.msPerDay);
		},
		axisParts() {
			return Array.from({length: 8}, (e, i) => i * CalendarDate.msPerDay);
		}
	},
	template: `
	<div class="fhc-calendar-mode-month-view h-100">
		<calendar-grid
			flip-axis
			:axis-main="axisMain"
			:axis-parts="axisParts"

			snap-to-grid
		>
			<template #main-header="{ timestamp }">
				<label-week v-bind="{ timestamp }" />
			</template>
			<template #part-header="{ part }">
				<label-dow :timestamp="axisMain[0] + part" />
			</template>
			<template #event="slot">
				<label-day
					v-if="slot.event.orig == 'header'"
					:timestamp="slot.event.start"
					:class="(new Date(slot.event.start)).getMonth() == month ? '' : 'disabled'"
				/>
				<slot v-else v-bind="slot" />
			</template>
		</calendar-grid>
	</div>
	`
}
