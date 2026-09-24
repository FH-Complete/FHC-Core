/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
import FhcCalendar from '../Calendar/Tempus.js';
import ApiKalender from '../../api/factory/tempus/kalender.js';
import ApiRenderers from '../../api/factory/renderers.js';
import ApiTempusConfig from '../../api/factory/tempus/config.js';

function getRangeFromQuery(query, timezone) {
	const startDate = Array.isArray(query?.startDate)
		? query.startDate[0]
		: query?.startDate;
	const endDate = Array.isArray(query?.endDate)
		? query.endDate[0]
		: query?.endDate;
	const start = luxon.DateTime.fromISO(startDate ?? '', {
		zone: timezone,
	}).startOf('day');
	const end = luxon.DateTime.fromISO(endDate ?? '', {
		zone: timezone,
	}).endOf('day');

	if (!start.isValid || !end.isValid || end < start)
		return null;

	return luxon.Interval.fromDateTimes(start, end);
}

function getPlanFilterFromQuery(query) {
	const filter = {};
	for (const key of ['ort', 'stg', 'uid']) {
		const value = Array.isArray(query?.[key]) ? query[key][0] : query?.[key];
		if (typeof value !== 'string') continue;

		try {
			filter[key] = JSON.parse(value);
		} catch {
			// Ignore malformed filter parameters.
		}
	}

	return filter;
}

export default {
	name: 'TempusCalendarPreview',
	components: {
		FhcCalendar,
	},
	props: {
		defaultSemester: String,
		config: Object,
		permissions: Object,
		tempusRoot: String,
		cisRoot: String,
		activeAddons: String, // semicolon separated list of active addons
		viewData: Object,
		logoutUrl: String,
		avatarUrl: String,
	},
	provide() {
		return {
			cisRoot: this.cisRoot,
			defaultSemester: this.defaultSemester,
			currentSemester: this.defaultSemester,
			renderers: Vue.computed(() => this.renderers),
			appConfig: Vue.computed(() => this.appconfig),
			canToggleGrid: this.permissions.stundenraster,
			isHeaderSticky: false
		};
	},
	data() {
		const today = luxon.DateTime.now().setZone(this.config.timezone).toISODate();

		return {
			appconfig: {},
			currentMode: 'multipleWeeks',
			configEndpoints: ApiTempusConfig,
			hoveredEvent: null,
			renderers: {},
			ort_kurzbz: null,
			parkedKeys: new Set(),
			lecturers: [],
			studiengaenge: [],
			rooms: [],
			overlayCache: {},
			extraBackgrounds: [],
			lastRange: null,
			visibleStatus: ['all'],
			selectedStudiensemester: this.defaultSemester,
			urlRange: null,
			planFilter: {},
			calendarDatesByMode: {
				week: today,
				month: today,
				tableList: today,
				range: today,
			},
			historyEntries: [],
			previewRole: 'planer',
			multiWeekModal: {
				show: false,
				lehreinheitId: null,
				ortKurzbz: null,
				startTime: null,
				endTime: null,
			},
			studiengaenge_all: [],
			maxDailyEventLimitForMonthView: 10,
			raumvorschlagPreview: null,
			raumvorschlagLoading: false,
			showEvents: true,
			reservierungPending: false,
			bcc: null,
			currentlyUpdatedEvent: null,
		};
	},
	computed: {
		calendarDate() {
			return (
				this.calendarDatesByMode[this.currentMode] ??
				this.calendarDatesByMode.week
			);
		},
		visibleLecturerUids() {
			if (!this.lecturers.length) return null;
			return this.lecturers
				.filter((lecture) => lecture.showEvents)
				.map((lecture) => lecture.uid);
		},
	},
	methods: {
		handleChangeDate(newDate) {
			if (!(newDate && luxon.DateTime.isDateTime(newDate) && newDate.isValid))
				return;

			this.calendarDatesByMode = {
				...this.calendarDatesByMode,
				[this.currentMode]: newDate.toISODate(),
			};
		},
		handleChangeMode(newMode, newDate) {
			if (!newMode) return;

			if (newDate && luxon.DateTime.isDateTime(newDate) && newDate.isValid) {
				this.calendarDatesByMode = {
					...this.calendarDatesByMode,
					[this.currentMode]: newDate.toISODate(),
				};
			}

			this.currentMode = newMode;
		},
		getPromiseFunc(start, end) {
			const collisionCheck = true;
			const filter = this.planFilter;

			let response = null;
			if (this.previewRole === 'lektor') {
				response = [
					this.$api.call(
						ApiKalender.getPlanLecturer(
							start.toISODate(),
							end.toISODate(),
							collisionCheck,
							
						),
					),
				];
			} else if (this.previewRole === 'student') {
				response = [
					this.$api.call(
						ApiKalender.getPlanStudent(
							start.toISODate(),
							end.toISODate(),
							collisionCheck,
							
						),
					),
				];
			} else {
				response = [
				this.$api.call(
					ApiKalender.getPlan(
						filter,
						start.toISODate(),
						end.toISODate(),
						collisionCheck,
						
					),
				),
			];
			}

			if (response) {
				response[0].then((result) => {
					this.scrollToAndEmphasizeUpdatedEvent();
				});
			}

			return response;
		},
		_parseDates(start, end) {
			const startDT = luxon.DateTime.fromISO(start);
			const endDT = luxon.DateTime.fromISO(end);

			if (!startDT.isValid || !endDT.isValid) {
				alert('Ungültiges Datum');
				return null;
			}

			return {
				startDT,
				endDT,
				start_time: startDT.toFormat('yyyy-MM-dd HH:mm'),
				end_time: endDT.toFormat('yyyy-MM-dd HH:mm'),
			};
		},

		_updateKalenderEvent(obj, startDT, endDT, start_time, end_time, onSuccess) {
			const origStart = luxon.DateTime.fromISO(obj.orig.isostart);
			const origEnd = luxon.DateTime.fromISO(obj.orig.isoend);

			if (
				origStart.toMillis() === startDT.toMillis() &&
				origEnd.toMillis() === endDT.toMillis()
			)
				return;

			const updatedInfos = {
				ort_kurzbz: this.rooms.length
					? this.rooms.map((room) => room.ort_kurzbz)
					: (obj.orig.ort_kurzbz ?? []),
				start_time,
				end_time,
			};

			return this.$api
				.call(
					ApiKalender.updateKalenderEvent(obj.orig.kalender_id, updatedInfos),
				)
				.then(() => {
					if (onSuccess) {
						onSuccess();
						this.$refs.calendar.$refs.calendar.$refs.mode.$refs.view.$refs.grid?.disableAutoScroll();
						this.$refs.calendar.$refs.calendar.$refs.mode.$refs.view.$refs.grids?.forEach(grid => {
							grid.disableAutoScroll();
						});
						this.currentlyUpdatedEvent = obj.orig;
					}
				})
				.catch((error) => {
					this.currentlyUpdatedEvent = null;
					this.$refs.calendar.clearOutCalendarEventEmphasis();
					this.$nextTick(() => {
						this.$refs.calendar.reloadEvents();
					});
					throw error;
				});
		},
		handleRange(range) {
			if (!range?.start || !range?.end) return;

			if (this.currentMode === 'week') {
				//Workaround because, updateRange is emitting 2 times
				const startDay = range.start.startOf('day');
				const endDay = range.end.startOf('day');

				const days = Math.round(endDay.diff(startDay, 'days').days) + 1;
				if (days > 8) return;
			}

			this.lastRange = range;

			const key = `${range.start.toISODate()}_${range.end.toISODate()}_${this.currentMode}`;

			for (const lect of this.lecturers) {
				this.getOverlays(lect.uid, range, key);
			}

			this.rebuildExtraBackgrounds();

			if (this.raumvorschlagPreview)
				this.previewRaumvorschlag({
					lehreinheit_id: this.raumvorschlagPreview.lehreinheit_id,
				});
		},

		getOverlays(uid, range, rangeKey) {
			if (!this.overlayCache[uid]) this.overlayCache[uid] = {};

			let entry = this.overlayCache[uid][rangeKey];

			if (entry?.loaded || entry?.loading) return;

			entry = this.overlayCache[uid][rangeKey] = {
				blocks: [],
				wishes: [],
				loading: true,
				loaded: false,
			};

			const promises = [];
			const lect = this.lecturers.find((lecture) => lecture.uid === uid);

			if (lect.overlays.wishes) {
				promises.push(
					this.$api
						.call(
							ApiKalender.getLektorZeitwuensche(
								uid,
								range.start.toISODate(),
								range.end.toISODate(),
							),
						)
						.then((result) => {
							entry.wishes = (result.data || []).map((zeitwunsch) => ({
								class: `bg-lecturer-wish bg-uid-${uid} wish-w-${zeitwunsch.gewicht}`,
								start: zeitwunsch.isostart,
								end: zeitwunsch.isoend,
								label: zeitwunsch.label,
							}));
						}),
				);
			}

			if (lect.overlays.blocks) {
				promises.push(
					this.$api
						.call(
							ApiKalender.getLektorZeitsperren(
								uid,
								range.start.toISODate(),
								range.end.toISODate(),
							),
						)
						.then((result) => {
							entry.blocks = (result.data || []).map((zeitsperre) => ({
								class: `bg-lecturer-block bg-uid-${uid}`,
								start: zeitsperre.isostart,
								end: zeitsperre.isoend,
								label: zeitsperre.label,
							}));
						}),
				);
			}

			Promise.allSettled(promises).then(() => {
				entry.loading = false;
				entry.loaded = true;
				this.rebuildExtraBackgrounds();
			});
		},

		rebuildExtraBackgrounds() {
			if (!this.lastRange) return;

			const key =
				`${this.lastRange.start.toISODate()}_` +
				`${this.lastRange.end.toISODate()}_` +
				`${this.currentMode}`;
			let res = [];

			for (let lect of this.lecturers) {
				const entry = this.overlayCache[lect.uid]?.[key];
				if (!entry) continue;

				if (lect.overlays.blocks) res.push(...(entry.blocks || []));

				if (lect.overlays.wishes) res.push(...(entry.wishes || []));
			}

			if (this.raumvorschlagPreview)
				res.push(...this.raumvorschlagPreview.slots);

			this.extraBackgrounds = res;
		},

		openReservierung() {
			this.reservierungPending = false;
			this.$refs.reservierung?.show();
		},
		onEventHover(event) {
			this.hoveredEvent = event;
		},
		onEventUnhover(event) {
			if (this.hoveredEvent?.kalender_id === event?.kalender_id) {
				this.hoveredEvent = null;
			}
		},
		scrollToAndEmphasizeUpdatedEvent(shouldEmphasize = true) {
			if (!this.currentlyUpdatedEvent) return;

			if (shouldEmphasize) {
				document
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
			}

			setTimeout(() => {
				const eventEl = document.querySelector(
					`[data-group-id="event-group-${this.currentlyUpdatedEvent.eindeutige_kalender_gruppen_id}"]`,
				);
				if (!eventEl) return;

				const calendar = document.querySelector('.fhc-calendar-base-grid');
				const eventRect = eventEl.getBoundingClientRect();

				const offset = 300;

				const isInsideScrolledView =
					eventEl.offsetLeft < calendar.scrollLeft + calendar.clientWidth &&
					eventEl.offsetLeft + eventEl.offsetWidth > calendar.scrollLeft &&
					eventEl.offsetTop < calendar.scrollTop + calendar.clientHeight &&
					eventEl.offsetTop + eventEl.offsetHeight > calendar.scrollTop;

				const rect = eventEl.getBoundingClientRect();
				if (!isInsideScrolledView) {
					eventEl.scrollIntoView({
						behavior: 'smooth',
						inline: 'center',
						block: 'nearest',
					});
				}

				if (shouldEmphasize) {
					let timeout = 0;
					let emphasizeUpdateClassName = isInsideScrolledView
						? 'updated-event'
						: 'updated-event-long';

					if (!isInsideScrolledView) timeout = 300;

					setTimeout(() => {
						eventEl.classList.add(emphasizeUpdateClassName);
					}, timeout);

					this.currentlyUpdatedEvent = null;
				}
			}, 100);
		},
		updateKalenderEventElementDisplay(calendarGruppenId, startDT, endDT) {
			if (!calendarGruppenId)
				return;

			let startOfDay = startDT.startOf('day');
			let newPotentialStart = startDT.diff(startOfDay).toMillis() ?? 1;
			let newPotentialEnd = endDT.diff(startOfDay).toMillis();

			const calendar = this.$refs.calendar?.$el;
			let element = calendar?.querySelector(
				`[data-group-id="event-group-${calendarGruppenId}"]`,
			);
			if (!element) return;

			const targetGridLine = [...calendar.querySelectorAll(
				'.fhc-calendar-base-grid-line',
			)].find((gridLine) => {
				const [rowStart, columnStart, rowEnd] = getComputedStyle(
					gridLine,
				).gridArea.split(' / ');

				return (
					rowStart === '1' &&
					columnStart === String(startDT.weekday) &&
					rowEnd === '-1'
				);
			});
			const changedDay =
				targetGridLine && element.parentElement !== targetGridLine;

			setTimeout(() => {
				if (!calendar.contains(element)) return;


				if (changedDay) {
					targetGridLine.insertBefore(element, null);
					element.classList.add('tempus-temporary-calendar-event');
				}

				element.style.gridRowStart = 't_' + newPotentialStart;
				element.style.gridRowEnd = 't_' + newPotentialEnd;

				element.scrollIntoView({
					behavior: 'smooth',
					inline: 'center',
					block: 'nearest',
				});
			}, 100);

			const outerDiv = document.createElement('div');
			outerDiv.className = 'spinner-overlay';

			const innerDiv = document.createElement('div');
			innerDiv.className = 'spinner';

			outerDiv.appendChild(innerDiv);

			element.appendChild(outerDiv);

		},
		clearTemporaryEvents() {
			const calendar = this.$refs.calendar?.$el;
			if (!calendar) return;
			
			const tempEvents = calendar.querySelectorAll(
				'.tempus-temporary-calendar-event',
			);
			tempEvents.forEach((event) => {
				event.remove();
			});
		}
	},
	watch: {
		'$route.query': {
			immediate: true,
			handler(query) {
				this.planFilter = getPlanFilterFromQuery(query);
				this.previewRole = Array.isArray(query?.previewRole)
					? query.previewRole[0]
					: query?.previewRole ?? 'planer';
				this.urlRange = getRangeFromQuery(query, this.config.timezone);
				const rangeStart = this.urlRange?.start.toISODate()
					?? luxon.DateTime.now().setZone(this.config.timezone).toISODate();

				this.calendarDatesByMode = {
					...this.calendarDatesByMode,
					range: rangeStart,
				};
			},
		},
		lecturers: {
			deep: true,
			handler() {
				this.rebuildExtraBackgrounds();
			},
		},
		rooms() {
			this.$refs.calendar.resetEventLoader();
		},
	},
	mounted() {
		this.reservierungPending = false;
		this.bcc = new BroadcastChannel('fhc-dnd');
		this.bcc.addEventListener('message', (e) => {
			if (e.data === 'dropped' && !this.reservierungPending)
				this.$refs.calendar.resetEventLoader();
		});
	},
	beforeUnmount() {
		this.bcc?.close();
	},
	async created() {
		await this.$api
			.call(ApiRenderers.loadTempusRenderers())
			.then((res) => res.data)
			.then((data) => {
				for (let rendertype of Object.keys(data)) {
					let modalTitle = null;
					let modalContent = null;
					let calendarEvent = null;
					if (data[rendertype].modalTitle)
						modalTitle = Vue.markRaw(
							Vue.defineAsyncComponent(
								() => import(data[rendertype].modalTitle),
							),
						);
					if (data[rendertype].modalContent)
						modalContent = Vue.markRaw(
							Vue.defineAsyncComponent(
								() => import(data[rendertype].modalContent),
							),
						);
					if (data[rendertype].calendarEvent)
						calendarEvent = Vue.markRaw(
							Vue.defineAsyncComponent(
								() => import(data[rendertype].calendarEvent),
							),
						);

					if (data[rendertype].calendarEventStyles) {
						var head = document.head;
						if (
							!head.querySelector(
								`link[href="${data[rendertype].calendarEventStyles}"]`,
							)
						) {
							var link = document.createElement('link');
							link.type = 'text/css';
							link.rel = 'stylesheet';
							link.href = data[rendertype].calendarEventStyles;
							head.appendChild(link);
						}
					}

					if (!this.renderers[rendertype]) {
						this.renderers[rendertype] = {};
					}
					this.renderers[rendertype].modalTitle = modalTitle;
					this.renderers[rendertype].modalContent = modalContent;
					this.renderers[rendertype].calendarEvent = calendarEvent;
				}
			});
	},
	template: /* html */ `
	<div
		class="tempus"
		data-cy="tempus-preview"
	>
		<fhc-calendar
			ref="calendar"
			:timezone="config.timezone"
			:get-promise-func="getPromiseFunc"
			:visible-status="visibleStatus"
			:date="calendarDate"
			:range="urlRange"
			:mode="currentMode"
			:modes="['multipleWeeks']"
			:parkedEvents="parkedKeys"
			:visible-lecturers="visibleLecturerUids"
			:show-events="showEvents"
			:is-event-dragging-enabled="false"
			:is-event-resizing-enabled="false"
			:isRangeVirtualScrollEnabled="false"
			:can-toggle-collision-check="false"
			@update:date="handleChangeDate"
			@update:mode="handleChangeMode"
			@event-hover="onEventHover"
			@event-unhover="onEventUnhover"
			@open-reservierung="openReservierung"
			:extra-backgrounds="extraBackgrounds"
			@update:range="handleRange"
			@events-reloaded="clearTemporaryEvents"
			class="responsive-calendar"
			:cache-multiplier="currentMode === 'week' ? 1 : 0"
		/>
	</div>`,
};
