import FhcCalendar from './Base.js';

import { useEventLoader } from '../../composables/Tempus/TempusEventLoader.js';

import ModeWeek from './Mode/Week.js';
import ModeMonth from './Mode/Month.js';
import ModeTable from './Mode/Table.js';
import ModeRange from './Mode/Range.js';
import ApiKalender from '../../api/factory/tempus/kalender.js';
import draggable from '../../directives/draggable.js';
import ApiStudiensemester from '../../api/factory/studiensemester.js';

function getRangeLength(range) {
	if (!(range instanceof luxon.Interval) || !range.isValid)
		return null;

	const start = range.start.startOf('day');
	const end = range.end.startOf('day');
	if (end < start) return null;

	return Math.floor(end.diff(start, 'days').days) + 1;
}

export default {
	name: 'CalendarTempus',
	components: {
		FhcCalendar,
	},
	provide() {
		return {
			rangeLength: Vue.computed(() => this.currentRangeLength),
			rangeViewPresets: Vue.computed(() => this.semesterRangePresets),
			rangeViewPreviewLink: Vue.computed(() => this.rangeViewPreviewLink),
			// rangeViewSelectedPreset: Vue.computed({
			// 	get: () => this.selectedRangePreset,
			// 	set: value => this.selectedRangePreset = value,
			// }),
		};
	},
	inject: {
		renderers: {from: 'renderers'},
		currentSemester: {
			from: 'currentSemester',
			default: null,
		},
		canToggleGrid: {
			from: 'canToggleGrid',
			default: false
		},
		appConfig: {
			from: 'appConfig',
			default: {
				visible_status: 'all'
			}
		},
		rangeLength: {
			default: 30,
		},
		shouldIncludeRangeMode: {
			type: Boolean,
			default: false,
		},
	},
	directives: {
		draggable,
	},
	props: {
		timezone: {
			type: String,
			required: true,
		},
		date: {
			type: [Date, String, Number, luxon.DateTime],
			default: luxon.DateTime.local(),
		},
		range: {
			type: luxon.Interval,
			default: null,
		},
		mode: {
			type: String,
			default: 'Week',
		},
		modes: {
			type: Array,
			default: () => ['week', 'month', 'tableList', 'range'],
		},
		getPromiseFunc: {
			type: Function,
			required: true,
		},
		cacheMultiplier: {
			type: Number,
			default: 1
		},
		waitForAllPromises: {
			type: Boolean,
			default: true,
		},
		parkedEvents: {
			type: Object,
			default: () => new Set(),
		},
		visibleLecturers: {
			type: Array,
			default: null,
		},
		extraBackgrounds: {
			type: Array,
			default: () => [],
		},
		visibleStatus: {
			type: Array,
			default: () => ['all'],
		},
		showEvents: {
			type: Boolean,
			default: true
		},
		isEventDraggingEnabled: {
			type: Boolean,
			default: true,
		},
		isEventResizingEnabled: {
			type: Boolean,
			default: true,
		},
		isRangeVirtualScrollEnabled: {
			type: Boolean,
			default: true,
		},
		canToggleCollisionCheck: {
			type: Boolean,
			default: true,
		},
		rangeViewPreviewLink: {
			type: String,
			default: null,
		}
	},
	emits: [
		"update:date",
		"update:mode",
		"update:range",
		"update:date-range",
		"drop",
		"resize",
		"event-hover",
		"event-unhover",
		"open-reservierung",
		"events-reloaded",
	],
	data() {
		return {
			visibleDates: null,
			eventReloadKey: 0,
			refreshEventsAfterReload: false,
			calendarModes: {
				week: Vue.markRaw(ModeWeek),
				month: Vue.markRaw(ModeMonth),
				tableList: Vue.markRaw(ModeTable),
				range: Vue.markRaw(ModeRange),
			},
			modeOptions: {
				day: {
					emptyMessage: Vue.computed(() => this.$p.t('lehre/noLvFound')),
					emptyMessageDetails: Vue.computed(() => this.$p.t('lehre/noLvFound')),
				},
				week: {
					collapseEmptyDays: false,
				},
			},
			currentMode: this.mode,
			teachingunits: null,
			hoursplan: null,
			showRaster: true,
			currentRangeLength: getRangeLength(this.range) ?? this.rangeLength,
			selectedRangePreset: null, //this.currentSemester, TODO: This is a hack to make the range mode work, but it should be fixed in the future
			semesterRangePresets: {
				label: null,
				presets: [],
			},
		};
	},
	watch: {
		range(range) {
			const rangeLength = getRangeLength(range);
			this.currentRangeLength = rangeLength ?? this.rangeLength;
		},
		events() {
			if (this.refreshEventsAfterReload) {
				this.eventReloadKey += 1;
				this.refreshEventsAfterReload = false;
			}

			this.$emit('events-reloaded');
		}
	},
	computed: {
		availableModes() {
			return this.modes.reduce((availableModes, mode) => {
				if (this.calendarModes[mode])
					availableModes[mode] = this.calendarModes[mode];

				return availableModes;
			}, {});
		},
		backgrounds() {
			let now = luxon.DateTime.now().setZone(this.timezone);

			let past = [];
			if (this.mode == 'Month')
			{
				past = [{
					class: 'background-past',
					end: now.startOf('day')
				}];
			}
			else if (this.mode == 'Range') {
				return [];
			} else
			{
				past = [{
					class: 'background-past',
					end: now,
					label: now.startOf('minute').toISOTime({ suppressSeconds: true, includeOffset: false })
				}];
			}

			return [...past, ...(this.extraBackgrounds || [])];
		},
		visibleEvents() {
			let list = this.events;

			if (
				this.isRangeVirtualScrollEnabled
				&& this.currentMode === 'range'
				&& Array.isArray(this.visibleDates)
			)
			{
				const visibleIntervals = this.visibleDates
					.map(date => luxon.Interval.fromDateTimes(
						luxon.DateTime.fromISO(date.start).setZone(this.timezone).minus({ days: 1 }),
						luxon.DateTime.fromISO(date.end).setZone(this.timezone).plus({ days: 1 })
					))
					.filter(interval => interval.isValid);

				list = list.filter(event => {
					const eventStart = luxon.DateTime.fromISO(event.isostart).setZone(this.timezone);
					const eventEnd = luxon.DateTime.fromISO(event.isoend).setZone(this.timezone);

					// Do not hide an event with an invalid date; it is safer to keep it
					// available for the renderer than to silently discard it.
					if (!eventStart.isValid || !eventEnd.isValid)
						return true;

					return visibleIntervals.some(interval =>
						eventStart < interval.end && eventEnd > interval.start
					);
				});
			}

			if (Array.isArray(this.visibleLecturers))
			{
				const visibleLectures = new Set(this.visibleLecturers);

				list = list.filter((event) => {
					if (!event.lektor?.length) return true;
					return event.lektor.some((lektor) =>
						visibleLectures.has(lektor.mitarbeiter_uid),
					);
				});
			}

			if (!this.visibleStatus.length || this.visibleStatus.includes('all'))
				return list;

			return list.filter((event) =>
				this.visibleStatus.includes(event.status_kurzbz),
			);
		},
	},
	methods: {
		eventStyle(event) {
			if (!event.farbe) return undefined;
			return '--event-bg:#' + event.farbe;
		},
		updateRange(rangeInterval) {
			if (this.currentMode === 'tableList')
				return;

			this.rangeInterval = rangeInterval;
			if (
				this.visibleDates === null
				&& rangeInterval instanceof luxon.Interval
				&& rangeInterval.isValid
			)
			{
				const start = rangeInterval.start.startOf('day');
				this.visibleDates = [{
					start: start.toISO(),
					end: start.plus({ days: 7 }).toISO()
				}];
			}
			this.$emit('update:range', rangeInterval);
		},
		handleDateRange({ start, end }) {
			this.rangeInterval = luxon.Interval.fromDateTimes(start.startOf('day'), end.endOf('day'));
			this.reset();
			this.$emit('update:range', this.rangeInterval);
			this.$emit('update:date-range', { start, end });
		},
		handleDateUpdate(newDate, newMode, newRangeLength) {
			if (Number.isFinite(newRangeLength))
				this.currentRangeLength = newRangeLength;

			this.$emit('update:date', newDate, newMode);
		},
		ondrop(payload){
			this.$emit('drop', payload);
		},
		onresize(payload) {
			this.$emit('resize', payload);
		},
		resetEventLoader(arePreviousEventsCleared = true) {
			this.reset(arePreviousEventsCleared);
		},
		reloadEvents() {
			this.refreshEventsAfterReload = true;
			this.resetEventLoader(false);
		},
		navigatePrev() {
			this.$refs.calendar.clickPrev();
		},
		navigateNext() {
			this.$refs.calendar.clickNext();
		},
		async fetchSemesters() {
					const semestersResponse = await this.$api.call(ApiStudiensemester.getAll());
					if (semestersResponse.meta.status === "success") {
						this.semesterRangePresets = {
							label: "View specific semester",
							presets: semestersResponse.data.map((semester) => {
								let startDate = luxon.DateTime.fromISO(semester.start);
								let endDate = luxon.DateTime.fromISO(semester.ende);
								return {
									startDate,
									endDate,
									name: semester.studiensemester_kurzbz,
									description: semester.bezeichnung,
								};
							})
						};
					}
				},
		clearOutCalendarEventEmphasis() {
			this.$refs.calendar.$el
				.querySelectorAll(
					'.fhc-calendar-base-grid .fhc-calendar-base-grid-line-event',
				)
				.forEach((el) => {
					const spinner = el.querySelector('.spinner-overlay');
					if (spinner) {
						spinner.remove();
					}

					el.classList.remove(
						'updating-event',
						'updated-event',
						'updated-event-long',
					);
				});
		},
	},
	setup(props, context) {
		const rangeInterval = Vue.ref(null);

		const { events, lv, reset } = useEventLoader(
			rangeInterval,
			props.getPromiseFunc,
			() => props.cacheMultiplier,
			undefined,
			props.waitForAllPromises,
		);

		Vue.watch(lv, (newValue) => {
			context.emit('update:lv', newValue);
		});

		return {
			rangeInterval,
			events,
			lv,
			reset,
		};
	},

	created() {
		this.$api.call(ApiKalender.getStunden()).then((res) => {
			return (this.teachingunits = res.data.map((el) => ({
				id: el.stunde,
				start: el.beginn,
				end: el.ende,
			})));
		});

		this.$api.call(ApiKalender.getCalendarHours()).then((res) => {
			this.hoursplan = {
				start: res.data.start,
				end: res.data.end,
			};
		});

		this.fetchSemesters();
	},
	template: /* html */ `
	<fhc-calendar
		ref="calendar"
		class="fhc-calendar-lvplan"
		:date="date"
		:modes="availableModes"
		:mode-options="modeOptions"
		:mode="mode"
		:timezone="timezone"
		:locale="$p.user_locale.value"
		:events="visibleEvents || []"
		:event-reload-key="eventReloadKey"
		:backgrounds="backgrounds"
		:time-grid="showRaster ? teachingunits : null"
		:hours-plan="hoursplan"
		show-btns
		:draggable-events="isEventDraggingEnabled"
		:resizable-events="isEventResizingEnabled"
		:on-drop="isEventDraggingEnabled && ['week', 'range'].includes(currentMode) ? ondrop : null"
		:on-resize="isEventResizingEnabled ? onresize : null"
		@update:date="handleDateUpdate"
		@update:mode="(newMode, newDate) => { currentMode = newMode; $emit('update:mode', newMode, newDate) }"
		@update:range="updateRange"
		@update:visible-dates="visibleDates = $event"
		@update:date-range="handleDateRange"
	>
		<template v-slot="{ event, mode }">
			<div
				:class="['event-type-' + event.type + ' ' + mode + 'PageContainer', { 'event--parked': parkedEvents.has(String(event.kalender_id)) }, {'event--opacity': !showEvents}]"
				:type="mode == 'day' ? 'button' : undefined"
 				:style="eventStyle(event)"
				@mouseenter="$emit('event-hover', event)"
				@mouseleave="$emit('event-unhover', event)"
			>
				<component
					v-if="mode == 'event'"
					:is="renderers[event.type]?.modalContent"
					:event="event"
				></component>
				<component
					v-else-if="mode == 'eventheader'"
					:is="renderers[event.type]?.modalTitle"
					:event="event"
				></component>
				<component
					v-else
					:is="renderers[event.type]?.calendarEvent"
					:event="event"
				></component>
			</div>
		</template>
		<template #actions>
			<div class="d-flex align-items-center gap-2">
				<div 
					class="d-flex align-items-center gap-2" 
					style="cursor:pointer"
					@click="showRaster = !showRaster"
					v-if="canToggleGrid"
				>
					<i :class="showRaster ? 'fa-solid fa-toggle-on text-primary' : 'fa-solid fa-toggle-off text-muted'"></i>
					<span class="form-check-label">Stundenraster</span>
				</div>
				<div
					v-if="isEventDraggingEnabled"
					class="d-flex align-items-center gap-2 "
					v-draggable:move.noimage="{ type: 'reservierung', id: null, orig: {} }"
					data-cy="reservationDragHandle"
				>
					<i 
						class="fa-solid fa-calendar-plus text-primary" 
						style="cursor:pointer"
						@click.stop="$emit('open-reservierung')"
					></i>
					<span>Reservierung</span>
				</div>
				<div
					v-if="canToggleCollisionCheck"
					class="d-flex align-items-center gap-2"
				>
					<i :class="appConfig.ignore_kollision ? 'fa-solid fa-triangle-exclamation text-danger' : 'fa-solid fa-circle-check text-success'"></i>
					<span class="form-check-label">
						{{ appConfig.ignore_kollision ? 'Kollisionscheck aus' : 'Kollisionscheck an' }}
					</span>
				</div>
			</div>
		</template>
	</fhc-calendar>`
}
