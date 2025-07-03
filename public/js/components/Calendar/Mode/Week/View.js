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
		timezone: "timezone",
		timeGrid: "timeGrid",
		collapseEmptyDays: "collapseEmptyDays"
	},
	props: {
		year: Number,
		week: Number
	},
	computed: {
		start() {
			return luxon.DateTime
				.fromObject({ localWeekNumber: this.week, localWeekYear: this.year }, { locale: this.locale })
				.startOf('week')
				.setZone(this.timezone, { keepLocalTime: true });
		},
		axisMain() {
			return Array.from({ length: 7 }, (e, i) => this.start.plus({ days: i }).toMillis());
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
