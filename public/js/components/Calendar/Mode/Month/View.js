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
						const startdate = this.axisMain[w].plus(this.axisParts[d]);
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
		events: "events"
	},
	props: {
		day: {
			type: luxon.DateTime,
			required: true
		}
	},
	computed: {
		axisMain() {
			const start = this.day.startOf('month').startOf('week', { useLocaleWeeks: true });
			return Array.from({ length: 6 }, (e, i) => start.plus({ weeks: i }));
		},
		axisParts() {
			return Array.from({ length: 8 }, (e, i) => luxon.Duration.fromObject({ days: i }));
		}
	},
	template: /* html */`
	<div class="fhc-calendar-mode-month-view h-100">
		<calendar-grid
			flip-axis
			:axis-main="axisMain"
			:axis-parts="axisParts"

			snap-to-grid
		>
			<template #main-header="{ date }">
				<label-week v-bind="{ date }" />
			</template>
			<template #part-header="{ part }">
				<label-dow :date="axisMain[0].plus(part)" class="text-center" />
			</template>
			<template #event="slot">
				<label-day
					v-if="slot.event.orig == 'header'"
					:date="slot.event.start"
					:class="{ disabled: day.month != slot.event.start.month }"
				/>
				<div v-else-if="slot.event.type == 'loading'" class="placeholder-glow opacity-50">
					<span class="placeholder w-100 fs-1" />
				</div>
				<slot v-else v-bind="slot" />
			</template>
		</calendar-grid>
	</div>
	`
}
