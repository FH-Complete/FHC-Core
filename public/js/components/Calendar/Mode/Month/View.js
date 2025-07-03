import CalendarGrid from '../../Base/Grid.js';
import LabelWeek from '../../Base/Label/Week.js';
import LabelDow from '../../Base/Label/Dow.js';
import LabelDay from '../../Base/Label/Day.js';

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
					const start = event.start.startOf('day');
					const end = event.end.plus({ days: 1 }).startOf('day');
					return {
						...event,
						start,
						end
					};
				});
				for (var w = 5; w > -1; w--) {
					for (var d = 6; d > -1; d--) {
						const start = this.axisMain[w] + this.axisParts[d];
						const startdate = luxon.DateTime.fromMillis(start).setZone(this.timezone);
						events.unshift({
							start: startdate,
							end: startdate.plus({ days: 1 }),
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
		timezone: "timezone",
		events: "events"
	},
	props: {
		year: Number,
		month: Number
	},
	computed: {
		axisMain() {
			const start = luxon.DateTime
				.fromObject({ month: this.month, year: this.year })
				.startOf('week')
				.setZone(this.timezone, { keepLocalTime: true });
			return Array.from({ length: 6 }, (e, i) => start.plus({ weeks: i }).toMillis());
		},
		axisParts() {
			const msPerDay = luxon.Duration.fromObject({ days: 1 }).toMillis();
			return Array.from({ length: 8 }, (e, i) => i * msPerDay);
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
					:timestamp="slot.event.start.ts"
					:class="{ disabled: month != slot.event.start.month }"
				/>
				<slot v-else v-bind="slot" />
			</template>
		</calendar-grid>
	</div>
	`
}
