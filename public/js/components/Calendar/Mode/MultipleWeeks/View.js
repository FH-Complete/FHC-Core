import CalendarGrid from '../../Base/Grid.js';
import LabelDay from '../../Base/Label/Day.js';
import LabelDow from '../../Base/Label/Dow.js';
import LabelTime from '../../Base/Label/Time.js';
import FormInput from "../../../Form/Input.js";


export default {
	name: "MultipleWeeksView",
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
		hoursPlan: { from: "hoursPlan", default: null },
		isHeaderSticky: { from: "isHeaderSticky", default: true },
	},
	props: {
		day: {
			type: luxon.DateTime,
			required: true
		},
		collapseEmptyDays: Boolean,
		rangeLength: Number,
		adjustColumnWidths: {
			type: Boolean,
			default: true,
		},
		syncGridWidths: {
			type: Boolean,
			default: true,
		},
	},
	data() {
		return {
			visibleDays: JSON.parse(localStorage.getItem('fhc-calender-visible-days')) ?? [true, true, true, true, true, true, true],
			gridWidthResizeObserver: null,
			gridWidthMutationObserver: null,
			gridHeightResizeObserver: null,
			observedGridElements: Vue.markRaw(new Set()),
			gridWidthAnimationFrame: null,
			gridWidthSyncing: false,
			gridResizeActive: false,
			sharedDayColumnWidths: null,
			largestDayColumnWidths: null,
			columnWidthSyncVersion: 0,
			freshColumnWidthSyncRequested: false,
			initialColumnWidthEventsSeen: false,
			initialColumnWidthSyncAnimationFrame: null,
			gridHeights: [],
		}
	},
	computed: {
		start() {
			return this.day.startOf('week', { useLocaleWeeks: true });
		},
		axisMainTest() {
			const rangeLength = Math.max(Number(this.rangeLength) || 1, 1);
			const end = this.day.plus({ days: rangeLength - 1 }).endOf('week', { useLocaleWeeks: true });
			const weekCount = Math.floor(end.diff(this.start, 'weeks').weeks) + 1;

			return Array.from({ length: weekCount }, (e, weekIndex) =>
				Array.from({ length: 7 }, (e, dayIndex) =>
					this.start.plus({ weeks: weekIndex, days: dayIndex }),
				),
			);
		},
		axisMain() {
			return this.axisMainTest[0] ?? [];
		},
		axisMainNextWeek() {
			return this.axisMainTest.slice(1);
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
				const start = this.hoursPlan?.start ?? 7;
				const end = this.hoursPlan?.end ?? 23;
				return Array.from({ length: end - start + 1 }, (e, i) => luxon.Duration.fromObject({ hours: i + start }));
			}
		},
		dayVisibility() {
			return this.axisMain.map(date => this.visibleDays[date.weekday -1])
		},
	},
	methods: {
		resetColumnWidthAdjustment() {
			this.sharedDayColumnWidths = null;
			this.largestDayColumnWidths = null;
		},
		resetGridWidthSync() {
			this.gridElements().forEach(grid => grid.style.removeProperty('min-width'));
		},
		gridElements() {
			const grids = Array.isArray(this.$refs.grids)
				? this.$refs.grids
				: [this.$refs.grids];

			return grids
				.map(grid => grid?.$el)
				.filter(el => el instanceof HTMLElement);
		},
		hasColumnWidthEvents() {
			const grids = Array.isArray(this.$refs.grids)
				? this.$refs.grids
				: [this.$refs.grids];

			return grids.some(grid => grid?.originalEvents?.some(
				event => event.type !== 'loading',
			));
		},
		startFreshColumnWidthSync() {
			this.columnWidthSyncVersion += 1;
			this.resetColumnWidthAdjustment();
			this.resetGridWidthSync();
			if (this.gridWidthAnimationFrame !== null) {
				cancelAnimationFrame(this.gridWidthAnimationFrame);
				this.gridWidthAnimationFrame = null;
			}

			this.freshColumnWidthSyncRequested = true;
			this.$nextTick(() => this.flushFreshColumnWidthSync());
		},
		resetColumnWidthsForEventReload() {
			this.initialColumnWidthEventsSeen = false;
			this.startFreshColumnWidthSync();
		},
		resetGridHeightsForEventReload() {
			this.gridHeights = [];
			this.$nextTick(() => this.initializeGridHeights());
		},
		flushFreshColumnWidthSync() {
			if (!this.freshColumnWidthSyncRequested || this.gridWidthSyncing)
				return;

			this.freshColumnWidthSyncRequested = false;
			this.queueGridWidthSync('fresh column width sync');
		},
		queueInitialColumnWidthSync() {
			this.initialColumnWidthSyncAnimationFrame = requestAnimationFrame(() => {
				this.initialColumnWidthSyncAnimationFrame = requestAnimationFrame(() => {
					this.initialColumnWidthSyncAnimationFrame = null;
					this.startFreshColumnWidthSync();
				});
			});
		},
		setGridHeight(index, height) {
			if (!Number.isFinite(height) || height <= 0)
				return;

			if (this.gridHeights[index] !== height)
				this.gridHeights.splice(index, 1, height);
		},
		recordGridHeight(grid) {
			const index = this.gridElements().indexOf(grid);
			if (index === -1)
				return;

			this.setGridHeight(
				index,
				Math.ceil(grid.getBoundingClientRect().height),
			);
		},
		syncGridHeightTracking() {
			const grids = this.gridElements();
			const currentGrids = new Set(grids);

			this.observedGridElements.forEach(grid => {
				if (!currentGrids.has(grid)) {
					this.gridHeightResizeObserver.unobserve(grid);
					this.observedGridElements.delete(grid);
				}
			});
			if (this.gridHeights.length > grids.length)
				this.gridHeights.splice(grids.length);

			grids.forEach((grid, index) => {
				if (!this.observedGridElements.has(grid)) {
					this.gridHeightResizeObserver.observe(grid);
					this.observedGridElements.add(grid);
					this.setGridHeight(
						index,
						Math.ceil(grid.getBoundingClientRect().height),
					);
				}
			});
		},
		initializeGridHeights() {
			const grids = this.gridElements();
			const firstGrid = grids[0];
			if (!firstGrid)
				return;

			const initialGridHeight = Math.ceil(firstGrid.getBoundingClientRect().height);
			if (!Number.isFinite(initialGridHeight) || initialGridHeight <= 0)
				return;

			this.gridHeights = grids.map(() => initialGridHeight);
			this.$nextTick(() => this.syncGridHeightTracking());
		},
		queueGridWidthSync(source = 'unknown') {

			if (
				(!this.adjustColumnWidths && !this.syncGridWidths)
				|| this.gridResizeActive
				|| this.gridWidthSyncing
				|| this.gridWidthAnimationFrame !== null
			)
				return;

			this.gridWidthAnimationFrame = requestAnimationFrame(() => {
				this.gridWidthAnimationFrame = null;
				this.synchronizeGridWidths(source);
			});
		},
		async synchronizeGridWidths(source = 'unknown') {
			if (
				(!this.adjustColumnWidths && !this.syncGridWidths)
				|| this.gridResizeActive
				|| this.gridWidthSyncing
			)
				return;

			this.gridWidthSyncing = true;
			const syncVersion = this.columnWidthSyncVersion;

			try {
				const grids = this.gridElements();
				if (!grids.length)
					return;

				const previousDayColumnWidths = this.sharedDayColumnWidths;
				const previousGridMinWidths = grids.map(grid => grid.style.minWidth);
				const restorePreviousWidths = () => {
					this.sharedDayColumnWidths = this.adjustColumnWidths
						? this.largestDayColumnWidths ?? previousDayColumnWidths
						: null;
					grids.forEach((grid, index) => {
						if (this.syncGridWidths)
							grid.style.minWidth = previousGridMinWidths[index];
						else
							grid.style.removeProperty('min-width');
					});
				};

				grids.forEach(grid => grid.style.removeProperty('min-width'));
				this.sharedDayColumnWidths = null;
				await this.$nextTick();
				if (syncVersion !== this.columnWidthSyncVersion)
					return;
				if (!this.adjustColumnWidths && !this.syncGridWidths) {
					this.resetColumnWidthAdjustment();
					this.resetGridWidthSync();
					return;
				}
				if (this.gridResizeActive) {
					restorePreviousWidths();
					return;
				}

				if (this.adjustColumnWidths) {
					const dayColumns = Array.from(
						{ length: this.axisMainTest[0]?.length ?? 0 },
						(_, index) => Math.ceil(Math.max(...grids.map(grid => {
							const column = grid.querySelectorAll(
								'.grid-body > .fhc-calendar-base-grid-line',
							)[index];
							return column
								? Math.max(column.getBoundingClientRect().width, column.scrollWidth)
								: 0;
						}))),
					);

					const previousLargestWidths = this.largestDayColumnWidths;
					this.largestDayColumnWidths = dayColumns.map((width, index) =>
						Math.max(
							width,
							previousLargestWidths?.[index] ?? 0,
						),
					);
					this.sharedDayColumnWidths = this.largestDayColumnWidths;
					await this.$nextTick();
					if (syncVersion !== this.columnWidthSyncVersion)
						return;
					if (!this.adjustColumnWidths && !this.syncGridWidths) {
						this.resetColumnWidthAdjustment();
						this.resetGridWidthSync();
						return;
					}
					if (this.gridResizeActive) {
						restorePreviousWidths();
						return;
					}
				}

				if (this.syncGridWidths) {
					const width = Math.ceil(Math.max(
						this.$el.clientWidth,
						...grids.map(grid => grid.scrollWidth),
					));

					grids.forEach(grid => {
						const minWidth = width + 'px';
						if (grid.style.minWidth !== minWidth)
							grid.style.minWidth = minWidth;
					});
				}

			} finally {
				this.gridWidthSyncing = false;
				this.flushFreshColumnWidthSync();
			}
		},
		onGridResizeState(evt) {
			this.gridResizeActive = !!evt.detail?.active;
		},
		isToday(date) {
			return date.hasSame(luxon.DateTime.now().setZone(this.timezone), 'day');
		},
		toggleDay(day)
		{
			this.visibleDays.splice(day, 1, !this.visibleDays[day])
			localStorage.setItem('fhc-calender-visible-days', JSON.stringify(this.visibleDays))
			this.$nextTick(() => this.queueGridWidthSync('day visibility'));
		},
	},
	watch: {
		adjustColumnWidths(enabled) {
			if (!enabled) {
				this.resetColumnWidthAdjustment();
				return;
			}

			this.$nextTick(() => this.queueGridWidthSync('column width adjustment enabled'));
		},
		syncGridWidths(enabled) {
			if (!enabled) {
				this.resetGridWidthSync();
				return;
			}

			this.$nextTick(() => this.queueGridWidthSync('grid width synchronization enabled'));
		},
	},
	mounted() {
		this.gridHeightResizeObserver = Vue.markRaw(new ResizeObserver(entries => {
			entries.forEach(entry => this.recordGridHeight(entry.target));
		}));
		this.initializeGridHeights();

		this.gridWidthResizeObserver = Vue.markRaw(new ResizeObserver(() => {
			this.queueGridWidthSync('ResizeObserver');
		}));
		this.gridWidthResizeObserver.observe(this.$el);

		this.gridWidthMutationObserver = Vue.markRaw(new MutationObserver(mutations => {
			const isResizeGhost = node => node.nodeType === Node.ELEMENT_NODE
				&& ( node.matches('.fhc-event-ghost') || node.matches(".fhc-resize-preview") );
			const relevantMutations = mutations.filter(mutation =>
				mutation.type === 'childList'
				&& [...mutation.addedNodes, ...mutation.removedNodes]
					.some(node => !isResizeGhost(node)),
			);

			if (!relevantMutations.length)
				return;

			this.$nextTick(() => {
				this.syncGridHeightTracking();
				if (!this.initialColumnWidthEventsSeen && this.hasColumnWidthEvents()) {
					this.initialColumnWidthEventsSeen = true;
					this.startFreshColumnWidthSync();
					return;
				}
				this.queueGridWidthSync('MutationObserver childList');
			});
		}));
		this.gridWidthMutationObserver.observe(this.$el, {
			childList: true,
			subtree: true,
		});

		if (this.hasColumnWidthEvents()) {
			this.initialColumnWidthEventsSeen = true;
			this.startFreshColumnWidthSync();
		} else {
			this.queueGridWidthSync('mounted');
		}
		this.queueInitialColumnWidthSync();
	},
	beforeUnmount() {
		this.gridWidthResizeObserver?.disconnect();
		this.gridWidthMutationObserver?.disconnect();
		this.gridHeightResizeObserver?.disconnect();
		if (this.gridWidthAnimationFrame !== null)
			cancelAnimationFrame(this.gridWidthAnimationFrame);
		if (this.initialColumnWidthSyncAnimationFrame !== null)
			cancelAnimationFrame(this.initialColumnWidthSyncAnimationFrame);
	},
	template: /* html */`
	<div
		class="fhc-calendar-mode-week-view h-100 overflow-auto"
		@fhc-calendar-grid-resize-state="onGridResizeState"
	>
		<div
			v-for="(week, index) in axisMainTest"
			:key="week[0].toISODate()"
			style="width: fit-content"
		>
			<calendar-grid
				ref="grids"
				:main-column-widths="sharedDayColumnWidths"
				:scrollable="false"
				:sticky-header="isHeaderSticky"
				:axis-main="week"
				:axis-parts="axisParts"
				:axis-main-collapsible="collapseEmptyDays"
				:snap-to-grid="!!timeGrid"
				all-day-events
				:day-visibility="dayVisibility"
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
							:checked="visibleDays[date.weekday - 1]"
							@change="toggleDay(date.weekday - 1)"
							@click.stop
						>
						</form-input>
					</div>
				</template>
				<template #part-header="{ part }">
					<label-time v-bind="{ part }" />
				</template>
				<template #event="slot">
					<div v-if="slot.event.type == 'loading'" class="placeholder-glow h-100 opacity-50">
						<span class="placeholder w-100 h-100" />
					</div>
					<slot v-else v-bind="slot" />
				</template>
			</calendar-grid>
			<div
				aria-hidden="true"
				style="height:.5rem;background-color:#000"
			></div>
		</div>
	</div>
	`
}
