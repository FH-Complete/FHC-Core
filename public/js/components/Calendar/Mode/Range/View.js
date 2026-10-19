import CalendarGrid from "../../Base/Grid.js";
import LabelDay from "../../Base/Label/Day.js";
import LabelDow from "../../Base/Label/Dow.js";
import LabelTime from "../../Base/Label/Time.js";
import FormInput from "../../../Form/Input.js";

export default {
	name: "RangeView",
	components: {
		CalendarGrid,
		LabelDay,
		LabelDow,
		LabelTime,
		FormInput
	},
	inject: {
		timeGrid: "timeGrid",
		timezone: "timezone",
		events: "events",
		hoursPlan: { from: "hoursPlan", default: null },
		isHeaderSticky: { from: "isHeaderSticky", default: true },
	},
	props: {
		day: {
			type: luxon.DateTime,
			required: true,
		},
		collapseEmptyDays: Boolean,
		rangeLength: Number,
	},
	data() {
		return {
			visibleDays: JSON.parse(localStorage.getItem('fhc-calender-visible-days')) ?? [true, true, true, true, true, true, true],
		}
	},
	computed: {
		daysGroupedByWeek() {
			const focusDayStartOfWeek = this.$props.day.startOf("week");
			const prefixedDaysCount = this.$props.day.weekday - 1;
			let rangeLength = this.$props.rangeLength + prefixedDaysCount;
			rangeLength = rangeLength + (7 - (rangeLength % 7));

			let axisMain = Array.from({ length: rangeLength }, (e, i) =>
				focusDayStartOfWeek.plus({ days: i }),
			);

			return Object.groupBy(axisMain, (day) => {
				return day.startOf("week").toISO().slice(0, 10);
			});
		},
		weekStartDays() {
			return Object.keys(this.daysGroupedByWeek).sort();
		},
		timeAxisSegments() {
			if (this.timeGrid) {
				return this.timeGrid.map(tu => {
					return {
						start: luxon.Duration.fromISOTime(tu.start),
						end: luxon.Duration.fromISOTime(tu.end)
					};
				});
			} else if (this.hoursPlan) {
				const start = this.hoursPlan?.start ?? 7;
				const end = this.hoursPlan?.end ?? 23;
				return Array.from({ length: end - start + 1 }, (e, i) => luxon.Duration.fromObject({ hours: i + start }));
			} else {
				 return Array.from({ length: 17 }, (e, i) => luxon.Duration.fromObject({ hours: i + 7 }));
			}
		},
		eventsGroupedByWeek() {
			return Object.groupBy(
				this.events.filter((event) => !event.orig.allDayEvent),
				(event) => {
					return event.start.startOf("week").toISO().slice(0, 10);
				},
			);
		},
		dayVisibility() {
			return this.daysGroupedByWeek[this.weekStartDays[0]].map(date => this.visibleDays[date.weekday -1])
		},
	},
	methods: {
		isToday(date) {
			return date.hasSame(
				luxon.DateTime.now().setZone(this.timezone),
				"day",
			);
		},
		toggleDay(day)
		{
			this.visibleDays.splice(day, 1, !this.visibleDays[day])
			localStorage.setItem('fhc-calender-visible-days', JSON.stringify(this.visibleDays))
			//this.$nextTick(() => this.queueGridWidthSync('day visibility'));
		},
	},
	template: /* html */ `
	<div class="fhc-calendar-mode-range-view h-100 overflow-y-scroll">
		<calendar-grid
			v-for="weekStartDay in weekStartDays"
			:key="weekStartDay"
			:axis-main="daysGroupedByWeek[weekStartDay]"
			:axis-parts="timeAxisSegments"
			:axis-main-collapsible="collapseEmptyDays"
			:snap-to-grid="!!timeGrid"
			:overwrittenEvents="
				weekStartDay in eventsGroupedByWeek ? eventsGroupedByWeek[weekStartDay] : []
			"
			:shouldMatchParentHeight="false"
			:day-visibility="dayVisibility"
			:sticky-header="isHeaderSticky"
		>
			<template #main-header="{ date }">
				<div :class="{ today: isToday(date) }" class="d-flex flex-column align-items-center">
					<label-dow
						v-bind="{ date }"
						@cal-click="evt => evt.detail.source = 'day'"
					/>
					<label-day
						v-bind="{ date }"
					/>
					<form-input
							type="checkbox"
							:checked="visibleDays[date.weekday - 1]"
							@change="toggleDay(date.weekday - 1)"
							@click.stop
						>
					</form-input>
				</div>
			</template>
			<template #part-header="{ part }">
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
	`,
};
