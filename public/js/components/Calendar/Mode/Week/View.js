import CalendarGrid from '../../Base/Grid.js';
import LabelDay from '../../Base/Label/Day.js';
import LabelDow from '../../Base/Label/Dow.js';
import LabelTime from '../../Base/Label/Time.js';

import CalendarDate from '../../../../helpers/Calendar/Date.js';

export default {
	name: "WeekView",
	components: {
		CalendarGrid,
		LabelDay,
		LabelDow,
		LabelTime
	},
	inject: {
		locale: "locale",
		timeGrid: "timeGrid",
		collapseEmptyDays: "collapseEmptyDays"
	},
	props: {
		year: Number,
		week: Number
	},
	computed: {
		axisMain() {
			return CalendarDate.getDaysInWeek(this.week, this.year, this.locale).map(d => d.getTime());
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
		}
	},
	template: `
	<div class="fhc-calendar-mode-week-view h-100">
		<calendar-grid
			:axis-main="axisMain"
			:axis-parts="axisParts"
			:axis-main-collapsible="collapseEmptyDays"

			:snap-to-grid="!!timeGrid"
		>
			<template #main-header="{ timestamp }">
				<label-dow
					v-bind="{ timestamp }"
					@cal-click="evt => evt.detail.source = 'day'"
				/>
				<label-day
					v-bind="{ timestamp }"
				/>
			</template>
			<template #part-header="{ part }">
				<label-time v-bind="{ part }" />
			</template>
			<template #event="slot">
				<slot v-bind="slot" />
			</template>
		</calendar-grid>
	</div>
	`
}
