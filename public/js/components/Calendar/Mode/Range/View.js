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
				resizeObserver: null,
				mutationObserver: null,
				animationFrame: null,
				syncing: false,
				isActive: false,
			},
			gridColumnWidthEqualizingData: {
				sharedDayWidths: null,
				largestDayWidths: null,
				syncVersion: 0,
				syncRequested: false,
				initialEventsSeen: false,
				initialSyncAnimationFrame: null,
			},
		};
	},
	watch: {
		areGridWidthsEqual(enabled) {
			if (!enabled) {
				this.clearGridMinWidths();
				return;
			}
			this.$nextTick(() => this.scheduleGridWidthSync());
		},
		areDayColumnWidthsEqual(enabled) {
			if (!enabled) {
				this.resetColumnWidthAdjustment();
				return;
			}
			this.$nextTick(() =>
				this.scheduleGridWidthSync('column width adjustment enabled'),
			);
		},
	},
	computed: {
		daysGroupedByWeek() {
			const focusDayStartOfWeek = this.$props.day.startOf('week');
			const prefixedDaysCount = this.$props.day.weekday - 1;
			let rangeLength = this.$props.rangeLength + prefixedDaysCount;
			rangeLength = rangeLength + (7 - (rangeLength % 7));

			let axisMain = Array.from({ length: rangeLength }, (e, i) =>
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
					scroll;
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
			this.$nextTick(() => this.scheduleGridWidthSync());
		},
		getGridElements() {
			const grids = Array.isArray(this.$refs.grids)
				? this.$refs.grids
				: [this.$refs.grids];
			return grids
				.map((grid) => grid?.$el)
				.filter((el) => el instanceof HTMLElement);
		},

		onGridResizeState(evt) {
			this.gridWidthEqualizingData.isActive = !!evt.detail?.active;
		},
		scheduleGridWidthSync() {
			if (
				(!this.areDayColumnWidthsEqual && !this.areGridWidthsEqual) ||
				this.gridWidthEqualizingData?.isActive ||
				this.gridWidthEqualizingData?.syncing ||
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
			if (
				(!this.areDayColumnWidthsEqual && !this.areGridWidthsEqual) ||
				this.gridWidthEqualizingData?.isActive ||
				this.gridWidthEqualizingData?.syncing
			)
				return;
			this.gridWidthEqualizingData.syncing = true;
			const syncVersion = this.gridColumnWidthEqualizingData.syncVersion;
			try {
				const grids = this.getGridElements();
				if (!grids.length) return;
				const previousDayColumnWidths =
					this.gridColumnWidthEqualizingData.sharedDayWidths;
				const previousGridMinWidths = grids.map((grid) => grid.style.minWidth);
				const restorePreviousWidths = () => {
					this.gridColumnWidthEqualizingData.sharedDayWidths = this
						.areDayColumnWidthsEqual
						? (this.gridColumnWidthEqualizingData.largestDayWidths ??
							previousDayColumnWidths)
						: null;
					grids.forEach((grid, index) => {
						if (this.areGridWidthsEqual)
							grid.style.minWidth = previousGridMinWidths[index];
						else grid.style.removeProperty('min-width');
					});
				};
				grids.forEach((grid) => grid.style.removeProperty('min-width'));
				this.gridColumnWidthEqualizingData.sharedDayWidths = null;
				await this.$nextTick();
				if (syncVersion !== this.gridColumnWidthEqualizingData.syncVersion)
					return;
				if (!this.areDayColumnWidthsEqual && !this.areGridWidthsEqual) {
					this.resetColumnWidthAdjustment();
					this.clearGridMinWidths();
					return;
				}
				if (this.gridWidthEqualizingData?.isActive) {
					restorePreviousWidths();
					return;
				}

				if (this.areDayColumnWidthsEqual) {
					let firstWeekColumnsCount =
						this.daysGroupedByWeek[this.weekStartDays[0]]?.length ?? 0;
					const dayColumns = Array.from(
						{ length: firstWeekColumnsCount },
						(_, index) =>
							Math.ceil(
								Math.max(
									...grids.map((grid) => {
										const column = grid.querySelectorAll(
											'.grid-body > .fhc-calendar-base-grid-line',
										)[index];
										return column
											? Math.max(
													column.getBoundingClientRect().width,
													column.scrollWidth,
												)
											: 0;
									}),
								),
							),
					);
					const previousLargestWidths =
						this.gridColumnWidthEqualizingData.largestDayWidths;
					this.gridColumnWidthEqualizingData.largestDayWidths = dayColumns.map(
						(width, index) =>
							Math.max(width, previousLargestWidths?.[index] ?? 0),
					);
					this.gridColumnWidthEqualizingData.sharedDayWidths =
						this.gridColumnWidthEqualizingData.largestDayWidths;
					await this.$nextTick();
					if (syncVersion !== this.gridColumnWidthEqualizingData.syncVersion)
						return;
					if (!this.areDayColumnWidthsEqual && !this.areGridWidthsEqual) {
						this.resetColumnWidthAdjustment();
						this.clearGridMinWidths();
						return;
					}
					if (this.gridWidthEqualizingData?.isActive) {
						restorePreviousWidths();
						return;
					}
				}
				if (this.areGridWidthsEqual) {
					const width = Math.ceil(
						Math.max(
							this.$el.clientWidth,
							...grids.map((grid) => grid.scrollWidth),
						),
					);
					grids.forEach((grid) => {
						const minWidth = width + 'px';
						if (grid.style.minWidth !== minWidth)
							grid.style.minWidth = minWidth;
					});
				}
			} finally {
				this.gridWidthEqualizingData.syncing = false;
				this.flushFreshColumnWidthSync();
			}
		},
		clearGridMinWidths() {
			this.getGridElements().forEach((grid) =>
				grid.style.removeProperty('min-width'),
			);
		},
		resetColumnWidthAdjustment() {
			this.gridColumnWidthEqualizingData.sharedDayWidths = null;
			this.gridColumnWidthEqualizingData.largestDayWidths = null;
		},
		hasColumnWidthEvents() {
			const grids = Array.isArray(this.$refs.grids)
				? this.$refs.grids
				: [this.$refs.grids];
			return grids.some((grid) =>
				grid?.originalEvents?.some((event) => event.type !== 'loading'),
			);
		},
		startFreshColumnWidthSync() {
			this.gridColumnWidthEqualizingData.syncVersion += 1;
			this.resetColumnWidthAdjustment();
			this.clearGridMinWidths();
			if (this.gridWidthEqualizingData.animationFrame !== null) {
				cancelAnimationFrame(this.gridWidthEqualizingData.animationFrame);
				this.gridWidthEqualizingData.animationFrame = null;
			}
			this.gridColumnWidthEqualizingData.syncRequested = true;
			this.$nextTick(() => this.flushFreshColumnWidthSync());
		},
		resetColumnWidthsForEventReload() {
			this.gridColumnWidthEqualizingData.initialEventsSeen = false;
			this.startFreshColumnWidthSync();
		},
		flushFreshColumnWidthSync() {
			if (
				!this.gridColumnWidthEqualizingData.syncRequested ||
				this.gridColumnWidthEqualizingData.syncing
			)
				return;
			this.gridColumnWidthEqualizingData.syncRequested = false;
			this.scheduleGridWidthSync();
		},
		queueInitialColumnWidthSync() {
			this.gridColumnWidthEqualizingData.initialSyncAnimationFrame =
				requestAnimationFrame(() => {
					this.gridColumnWidthEqualizingData.initialSyncAnimationFrame =
						requestAnimationFrame(() => {
							this.gridColumnWidthEqualizingData.initialSyncAnimationFrame =
								null;
							this.startFreshColumnWidthSync();
						});
				});
		},
	},
	mounted() {
		this.gridWidthEqualizingData.resizeObserver = Vue.markRaw(
			new ResizeObserver(() => {
				this.scheduleGridWidthSync();
			}),
		);
		this.gridWidthEqualizingData.resizeObserver.observe(this.$el);
		this.gridWidthEqualizingData.mutationObserver = Vue.markRaw(
			new MutationObserver((mutations) => {
				const isResizeGhost = (node) =>
					node.nodeType === Node.ELEMENT_NODE &&
					(node.matches('.fhc-event-ghost') ||
						node.matches('.fhc-resize-preview'));
				const relevantMutations = mutations.filter(
					(mutation) =>
						mutation.type === 'childList' &&
						[...mutation.addedNodes, ...mutation.removedNodes].some(
							(node) => !isResizeGhost(node),
						),
				);
				if (!relevantMutations.length) return;
				this.$nextTick(() => {
					if (
						!this.gridColumnWidthEqualizingData.initialEventsSeen &&
						this.hasColumnWidthEvents()
					) {
						this.gridColumnWidthEqualizingData.initialEventsSeen = true;
						this.startFreshColumnWidthSync();
						return;
					}
					this.scheduleGridWidthSync();
				});
			}),
		);
		this.gridWidthEqualizingData?.mutationObserver.observe(this.$el, {
			childList: true,
			subtree: true,
		});

		if (this.hasColumnWidthEvents()) {
			this.gridColumnWidthEqualizingData.initialEventsSeen = true;
			this.startFreshColumnWidthSync();
		} else {
			this.scheduleGridWidthSync();
		}
		this.queueInitialColumnWidthSync();
	},
	beforeUnmount() {
		this.gridWidthEqualizingData?.resizeObserver?.disconnect();
		this.gridWidthEqualizingData?.mutationObserver?.disconnect();

		if (this.gridWidthEqualizingData.animationFrame !== null)
			cancelAnimationFrame(this.gridWidthEqualizingData.animationFrame);
		if (this.gridColumnWidthEqualizingData.initialSyncAnimationFrame !== null)
			cancelAnimationFrame(
				this.gridColumnWidthEqualizingData.initialSyncAnimationFrame,
			);
	},
	template: /* html */ `
	<div 
		class="fhc-calendar-mode-range-view h-100 overflow-y-scroll"
		@fhc-calendar-grid-resize-state="onGridResizeState"
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
