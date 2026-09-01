import CalendarGrid from '../../Base/Grid.js';
import LabelDay from '../../Base/Label/Day.js';
import LabelDow from '../../Base/Label/Dow.js';
import LabelTime from '../../Base/Label/Time.js';

export default {
	name: "RangeView",
	components: {
		CalendarGrid,
		LabelDay,
		LabelDow,
		LabelTime
	},
	inject: {
		timeGrid: "timeGrid",
		timezone: "timezone"
	},
	props: {
		day: {
			type: luxon.DateTime,
			required: true
		},
		collapseEmptyDays: Boolean,
		rangeLength: Number,
	},
	computed: {
		axisMain() {
			return Array.from({ length: this.$props.rangeLength }, (e, i) => this.$props.day.plus({ days: i }));
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
		}
	},
	methods: {
		isToday(date) {
			return date.hasSame(luxon.DateTime.now().setZone(this.timezone), 'day');
		}
	},
	template: /* html */`
	<div class="fhc-calendar-mode-range-view h-100">
		<calendar-grid
			ref="grid"
			:axis-main="axisMain"
			:axis-parts="axisParts"
			:axis-main-collapsible="collapseEmptyDays"
			:snap-to-grid="!!timeGrid"
			:flip-axis="true"
			:arePartHeadersRepeated="true"
			all-day-events
		>
			<template #main-header="{ date }">
				<div :class="{ today: isToday(date) }">
					<label-dow
						v-bind="{ date }"
						@cal-click="evt => evt.detail.source = 'day'"
					/>
					<label-day
						v-bind="{ date }"
					/>
				</div>
			</template>
			<template #part-header="{ part }">
				<label-time v-bind="{ part }" :alignItemsClassSuffix="'center'" />
			</template>
			<template #part-header-repeat="{ part }">
				<label-time v-bind="{ part }" :alignItemsClassSuffix="'center'" />
			</template>
			<template #event="slot">
				<div v-if="slot.event.type == 'loading'" class="placeholder-glow h-100 opacity-50">
					<span class="placeholder w-100 h-100" />
				</div>
				<slot v-else v-bind="slot" />
			</template>
		</calendar-grid>
	</div>
	`
}
