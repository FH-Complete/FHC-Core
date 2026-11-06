import CalendarGrid from '../../Base/Grid.js';
import LabelDay from '../../Base/Label/Day.js';
import LabelDow from '../../Base/Label/Dow.js';
import LabelTime from '../../Base/Label/Time.js';
import FormInput from '../../../Form/Input.js';

export default {
	name: 'RangeView',
	components: {
		CalendarGrid,
		LabelDay,
		LabelDow,
		LabelTime,
		FormInput,
	},
	inject: {
		timeGrid: 'timeGrid',
		timezone: 'timezone',
		events: 'events',
		hoursPlan: { from: 'hoursPlan', default: null },
		isHeaderSticky: { from: 'isHeaderSticky', default: true },
		areDayColumnWidthsEqual: {
			from: 'areDayColumnWidthsEqual',
			default: true,
		},
		areGridWidthsEqual: {
			from: 'areGridWidthsEqual',
			default: true,
		},
		isInitialColumnWidthSyncPostponed: {
			from: 'isInitialColumnWidthSyncPostponed',
			default: false,
		},
		columnWidthAdjustmentResetVersion: {
			from: 'columnWidthAdjustmentResetVersion',
			default: 0,
		},
		isDayVisibilityCheckShown: {
			from: 'isDayVisibilityCheckShown',
			default: true,
		}
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
			dayVisibilityStates: JSON.parse(
				localStorage.getItem('fhc-calender-visible-days'),
			) ?? [true, true, true, true, true, true, true],
			gridWidthEqualizingData: {
				animationFrame: null,
				isWidthEqualizingRunning: false,
			},
			gridColumnWidthEqualizingData: {
				sharedDayWidths: null,
				maxDayWidths: Array(7).fill(0),
				syncVersion: 0,
				isSyncRequested: false,
			},
		};
	},
	watch: {
		areGridWidthsEqual(enabled) {
			if (!enabled) {
				this.clearGridMinWidths();
				return;
			}
			
			this.scheduleGridWidthSync();
		},
		areDayColumnWidthsEqual(enabled) {
			if (!enabled) {
				this.resetColumnWidthAdjustment();
				return;
			}
			
			this.scheduleGridWidthSync();
		},
		events:{
			handler() {				
				if (this.events.length === 0 || this.events.every(event => event.type === 'loading')) {
					return;
				}

				if (this.isInitialColumnWidthSyncPostponed) {
					this.isInitialColumnWidthSyncPostponed = false;

					setTimeout(() => {
						this.startFreshColumnWidthSync();
					}, 200);
					return;
				}
				this.startFreshColumnWidthSync();
			},
			flush: 'post',
			deep: true,
		},
		weekStartDays: {
			handler() {
				this.startFreshColumnWidthSync();
			},
			flush: 'post',
		},
		timeAxisSegments: {
			handler() {
				this.startFreshColumnWidthSync();
			},
			flush: 'post',
		},
		dayVisibilityStates: {
			handler() {
				this.startFreshColumnWidthSync();
			},
			flush: 'post',
			deep: true,
		},
		columnWidthAdjustmentResetVersion: {
			handler() {
				this.resetColumnWidthAdjustment();
				this.clearGridMinWidths();
			},
			flush: 'post',
		},
	},
	computed: {
		daysGroupedByWeek() {
			const focusDayStartOfWeek = this.$props.day.startOf('week');
			const prefixedDaysCount = this.$props.day.weekday - 1;

			const totalDays =
				Math.ceil((this.$props.rangeLength + prefixedDaysCount) / 7) * 7;

			let axisMain = Array.from({ length: totalDays }, (e, i) =>
				focusDayStartOfWeek.plus({ days: i }),
			);

			return Object.groupBy(axisMain, (day) => {
				return day.startOf('week').toISO().slice(0, 10);
			});
		},
		weekStartDays() {
			return Object.keys(this.daysGroupedByWeek).sort();
		},
		timeAxisSegments() {
			if (this.timeGrid) {
				return this.timeGrid.map((tu) => {
					return {
						start: luxon.Duration.fromISOTime(tu.start),
						end: luxon.Duration.fromISOTime(tu.end),
					};
				});
			} else if (this.hoursPlan) {
				const start = this.hoursPlan?.start ?? 7;
				const end = this.hoursPlan?.end ?? 23;
				return Array.from({ length: end - start + 1 }, (e, i) =>
					luxon.Duration.fromObject({ hours: i + start }),
				);
			} else {
				return Array.from({ length: 17 }, (e, i) =>
					luxon.Duration.fromObject({ hours: i + 7 }),
				);
			}
		},
		eventsGroupedByWeek() {
			return Object.groupBy(
				this.events.filter((event) => !event.orig.allDayEvent),
				(event) => {
					return event.start.startOf('week').toISO().slice(0, 10);
				},
			);
		},
	},
	methods: {
		isToday(date) {
			return date.hasSame(luxon.DateTime.now().setZone(this.timezone), 'day');
		},
		toggleDayVisibility(day) {
			this.dayVisibilityStates.splice(day, 1, !this.dayVisibilityStates[day]);
			localStorage.setItem(
				'fhc-calender-visible-days',
				JSON.stringify(this.dayVisibilityStates),
			);
		},
		scheduleGridWidthSync() {
			if (
				(!this.areDayColumnWidthsEqual && !this.areGridWidthsEqual) ||
				this.gridWidthEqualizingData?.isWidthEqualizingRunning ||
				this.gridWidthEqualizingData.animationFrame !== null
			)
				return;

			this.gridWidthEqualizingData.animationFrame = requestAnimationFrame(
				() => {
					this.gridWidthEqualizingData.animationFrame = null;
					this.synchronizeGridWidths();
				},
			);
		},
		async synchronizeGridWidths() {
			if ((!this.areDayColumnWidthsEqual && !this.areGridWidthsEqual) ||
				this.gridWidthEqualizingData.isWidthEqualizingRunning) return;

			this.gridWidthEqualizingData.isWidthEqualizingRunning = true;
			const syncVersion = this.gridColumnWidthEqualizingData.syncVersion;
			const data = this.gridColumnWidthEqualizingData;

			try {
				let grids = this.getGridElements();
				if (!grids.length) return;

				// 1. Remove previously applied constraints so we measure natural sizes
				this.clearGridMinWidths();
				if (this.areDayColumnWidthsEqual) {
					data.sharedDayWidths = null;
					await this.$nextTick();
					if (syncVersion !== data.syncVersion) return;
					grids = this.getGridElements();
				}

				// 2. Equalize columns from the *current* natural widths (no history max)
				if (this.areDayColumnWidthsEqual) {
					const count = this.daysGroupedByWeek[this.weekStartDays[0]]?.length ?? 0;
					const widths = Array.from({ length: count }, (_, i) =>
						Math.ceil(Math.max(0, ...grids.map(grid => {
							const col = grid.querySelectorAll('.grid-body > .fhc-calendar-base-grid-line')[i];
							return col ? col.scrollWidth : 0; // one metric, not max of two
						})))
					);
					data.maxDayWidths = widths;
					data.sharedDayWidths = [...widths];
					await this.$nextTick();
					if (syncVersion !== data.syncVersion) return;
				}

				// 3. Equalize grid widths from the natural (unconstrained) grids
				if (this.areGridWidthsEqual) {
					const natural = Math.max(...grids.map(g => g.scrollWidth));
					const target = Math.max(this.$el.clientWidth, natural);
					grids.forEach(g => { g.style.minWidth = target + 'px'; });
				}
			} finally {
				this.gridWidthEqualizingData.isWidthEqualizingRunning = false;
				this.flushFreshColumnWidthSync();
			}
		},
		startFreshColumnWidthSync() {
			if (!this.areDayColumnWidthsEqual && !this.areGridWidthsEqual) return;

			this.gridColumnWidthEqualizingData.syncVersion += 1;

			if (this.gridWidthEqualizingData.animationFrame !== null) {
				cancelAnimationFrame(this.gridWidthEqualizingData.animationFrame);
				this.gridWidthEqualizingData.animationFrame = null;
			}
			this.gridColumnWidthEqualizingData.isSyncRequested = true;
			this.$nextTick(() => this.flushFreshColumnWidthSync());
		},
		resetColumnWidthAdjustment() {
			this.gridColumnWidthEqualizingData.sharedDayWidths = Array(7).fill(null);
			this.gridColumnWidthEqualizingData.maxDayWidths = Array(7).fill(0);

		},
		clearGridMinWidths() {
			this.getGridElements().forEach((grid) => {
				grid.style.removeProperty('min-width');
			});
		},
		flushFreshColumnWidthSync() {
			if (
				!this.gridColumnWidthEqualizingData.isSyncRequested ||
				this.gridWidthEqualizingData.isWidthEqualizingRunning
			)
				return;
			this.gridColumnWidthEqualizingData.isSyncRequested = false;
			this.scheduleGridWidthSync();
		},
		getGridElements() {
			const grids = Array.isArray(this.$refs.grids)
				? this.$refs.grids
				: [this.$refs.grids];
			return grids
				.map((grid) => grid?.$el)
				.filter((el) => el instanceof HTMLElement);
		},
	},
	mounted() {
		this.startFreshColumnWidthSync();
	},
	beforeUnmount() {
		if (this.gridWidthEqualizingData.animationFrame !== null) {
			cancelAnimationFrame(this.gridWidthEqualizingData.animationFrame);
			this.gridWidthEqualizingData.animationFrame = null;
		}
	},
	template: /* html */ `
	<div 
		class="fhc-calendar-mode-range-view h-100 overflow-y-scroll"
	>
		<div
			v-for="(weekStartDay, index) in weekStartDays"
			:key="weekStartDay"
			style="width: fit-content"
		>
			<calendar-grid
				ref="grids"
				:axis-main="daysGroupedByWeek[weekStartDay]"
				:axis-parts="timeAxisSegments"
				:axis-main-collapsible="collapseEmptyDays"
				:snap-to-grid="!!timeGrid"
				:overwrittenEvents="
					weekStartDay in eventsGroupedByWeek ? eventsGroupedByWeek[weekStartDay] : []
				"
				:shouldMatchParentHeight="false"
				:day-visibility="dayVisibilityStates"
				:sticky-header="isHeaderSticky"
				:scrollable="false"
				:main-column-widths="gridColumnWidthEqualizingData.sharedDayWidths"
				all-day-events
			>
				<template #main-header="{ date, index }">
					<div :class="{ today: isToday(date) }" class="d-flex flex-column align-items-center">
						<label-dow
							v-bind="{ date }"
							@cal-click="evt => evt.detail.source = 'day'"
						/>
						<label-day
							v-bind="{ date }"
						/>
						<form-input
								v-if="isDayVisibilityCheckShown"
								type="checkbox"
								:checked="dayVisibilityStates[date.weekday - 1]"
								@change="toggleDayVisibility(date.weekday - 1)"
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
	</div>
	`,
};
