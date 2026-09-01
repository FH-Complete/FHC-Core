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
import ApiSearchbar from '../../api/factory/searchbar.js';
import ApiRenderers from '../../api/factory/renderers.js';
import ApiTempusConfig from '../../api/factory/tempus/config.js';
import drop from '../../directives/drop.js';
import AppConfig from '../AppConfig.js';
import ApiStudiengangTree from '../../api/factory/tempus/studiengangtree.js';
import ApiInfo from '../../api/factory/tempus/info.js';
import Reservierung from './Reservierung.js';
import { getTempusShortcuts } from './shortcuts.js';
import KeyboardShortcuts from './KeyboardShortcuts.js';
import { useContextMenuActions } from '../../composables/Tempus/ContextMenuActions.js';
import MultiWeekPlanModal from './MultiWeekPlanModal.js';
import HistoryModal from './HistoryModal.js';
import ResourcesAssignmentModal from './ResourcesAssignmentModal.js';
import TagsAssignmentModal from './TagsAssignmentModal.js';
import { getTempusSearchbarOptions } from './Filters/searchbarOptions.js';
import TempusHeader from './Header.js';
import TempusAppMenu from './AppMenu.js';
import TempusVerbandMenu from './VerbandMenu.js';
import TempusSidebarMenu from './SidebarMenu.js';
import RaumauswahlModal from './RaumauswahlModal.js';
import LehreinheitModal from './LehreinheitModal.js';

export default {
	name: 'Tempus',
	components: {
		FhcCalendar,
		AppConfig,
		TempusAppMenu,
		TempusHeader,
		TempusVerbandMenu,
		TempusSidebarMenu,
		Reservierung,
		KeyboardShortcuts,
		MultiWeekPlanModal,
		HistoryModal,
		ResourcesAssignmentModal,
		TagsAssignmentModal,
		RaumauswahlModal,
		LehreinheitModal,
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
	directives: {
		drop,
	},
	provide() {
		return {
			cisRoot: this.cisRoot,
			defaultSemester: this.defaultSemester,
			currentSemester: this.defaultSemester,
			renderers: Vue.computed(() => this.renderers),
			appConfig: Vue.computed(() => this.appconfig),
			contextMenuActions: useContextMenuActions({
				openRaumauswahl: (orig) => this.$refs.raumModal.show(orig),
				openLehreinheit: (orig) => this.$refs.lehreinheitModal.show(orig),
				openResourcesAssignmentModal: (orig) =>
					this.$refs.resourcesAssignmentModal?.open(orig),
				openTagsModal: (orig) => this.$refs.tagsAssignmentModal?.open(orig),
				openHistory: (orig) => this.openHistory(orig),
				deleteEntry: (orig) => this.deleteEntry(orig),
				syncToLecturer: (orig) => this.syncToLecturer(orig),
				syncToStudent: (orig) => this.syncToStudent(orig),
			}),
			tableActions: {
				deleteEntries: (origList) => this.deleteEntries(origList),
				openRaumauswahl: (orig) => this.$refs.raumModal.show(orig),
			},
		};
	},
	data() {
		return {
			appconfig: {},
			currentMode: 'week',
			configEndpoints: ApiTempusConfig,
			endpoint: ApiStudiengangTree,
			raumVorschlaege: [],
			selected: [],

			lv_id: null,
			events: null,
			minimized: false,
			currentlySelectedEvent: null,
			hoveredEvent: null,
			studiensemesterKurzbz: this.defaultSemester,
			lists: {
				nations: [],
				sprachen: [],
				geschlechter: [],
			},
			renderers: null,
			ort_kurzbz: null,
			view: 'room',
			parkedKeys: new Set(),
			lecturers: [],
			studiengaenge: [],
			rooms: [],
			overlayCache: [],
			extraBackgrounds: [],
			lastRange: null,
			stg: null,
			semester: null,
			studiensemester_kurzbz: null,
			visibleStatusArray: {},
			visibleStatus: ['all'],
			selectedStudiensemester:
				this.studiensemester_kurzbz ?? this.defaultSemester,
			calendarDate: luxon.DateTime.now()
				.setZone(this.config.timezone)
				.toISODate(),
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
		};
	},
	computed: {
		currentDay() {
			return luxon.DateTime.now().setZone(this.config.timezone).toISODate();
		},
		visibleLecturerUids() {
			if (!this.lecturers.length) return null;
			return this.lecturers
				.filter((lecture) => lecture.showEvents)
				.map((lecture) => lecture.uid);
		},
		visibleStatusOptions() {
			return Object.entries(this.visibleStatusArray).map(([key, label]) => ({
				key,
				label,
			}));
		},
		visibleStatusValue() {
			if (this.visibleStatus.includes('all'))
				return this.visibleStatusOptions.filter(
					(visibleStatus) => visibleStatus.key === 'all',
				);
			return this.visibleStatus.map((status) => ({
				key: status,
				label: this.visibleStatusArray[status],
			}));
		},
		keyboardShortcuts() {
			return getTempusShortcuts(this);
		},
		searchbaroptions() {
			return getTempusSearchbarOptions(this);
		},
	},
	methods: {
		async deleteEntry(orig) {
			if (!orig?.kalender_id) return;

			await this.deleteEntryCall(orig);
			this.$refs.calendar.resetEventLoader();
			this.$refs.sidebar.reloadCoursepicker();
		},
		async deleteEntries(origList) {
			let validList = (origList ?? []).filter((orig) => orig?.kalender_id);
			if (!validList.length) return;

			await Promise.allSettled(
				validList.map((orig) => this.deleteEntryCall(orig)),
			);
			this.$refs.calendar.resetEventLoader();
			this.$refs.sidebar.reloadCoursepicker();
		},
		async deleteEntryCall(orig) {
			await this.$api
				.call(ApiKalender.deleteEntry(orig.kalender_id))
				.then(() => {
					this.$refs.sidebar.unpark({ type: orig.type, id: orig.kalender_id });
				});
		},
		async openHistory(orig) {
			if (!orig?.kalender_id) return;
			await this.$api
				.call(ApiKalender.getHistory(orig.kalender_id))
				.then((result) => {
					this.historyEntries = result.data ?? [];
					this.$refs.historyModal.show();
				});
		},
		syncToLecturer(orig) {
			if (!orig?.kalender_id) return;
			return this.$api
				.call(ApiKalender.syncToLecturer(orig.kalender_id))
				.then(() => this.$refs.calendar.resetEventLoader());
		},
		syncToStudent(orig) {
			if (!orig?.kalender_id) return;
			return this.$api
				.call(ApiKalender.syncToStudent(orig.kalender_id))
				.then(() => this.$refs.calendar.resetEventLoader());
		},
		setOrt: function (data) {
			this.ort_kurzbz = data.ort_kurzbz;
			this.rooms = [{ ort_kurzbz: data.ort_kurzbz }];
		},
		onSelectVerbandAndClose(payload) {
			this.onSelectVerband(payload);

			const verbandMenu = this.$refs.verbandMenu?.$el;
			if (verbandMenu)
				bootstrap.Offcanvas.getOrCreateInstance(verbandMenu).hide();
		},
		onSelectVerband({ path, stg_kz, semester, orgform_kurzbz, name }) {
			let exists = this.studiengaenge.some(
				(stg) =>
					stg.stg_kz == stg_kz &&
					stg.semester == semester &&
					(!orgform_kurzbz || stg.orgform_kurzbz === orgform_kurzbz),
			);

			if (!exists) {
				this.studiengaenge = [
					...this.studiengaenge,
					{ stg_kz, semester, orgform_kurzbz, name },
				];
			}
		},
		setEmp: function (data) {
			const uid = data.uid;
			const label = data.name;

			for (const lect of this.lecturers) delete this.overlayCache[lect.uid];

			this.lecturers = [
				{
					uid,
					label,
					showEvents: true,
					overlays: { blocks: true, wishes: true },
				},
			];

			this.$refs.calendar.resetEventLoader();
			if (this.lastRange) this.handleRange(this.lastRange);
		},
		addToFilter: function (filter, type) {
			if (type === 'ort') {
				const ort_kurzbz = filter.ort_kurzbz;
				if (!this.rooms.some((room) => room.ort_kurzbz === ort_kurzbz)) {
					this.rooms.push({ ort_kurzbz });
				}
			} else if (type === 'mitarbeiter') {
				const uid = filter.uid;
				const label = filter.name;
				if (!this.lecturers.some((l) => l.uid === uid)) {
					this.lecturers.push({
						uid,
						label,
						showEvents: true,
						overlays: { blocks: true, wishes: true },
					});
				}
			}

			this.$refs.calendar.resetEventLoader();
			if (this.lastRange) this.handleRange(this.lastRange);
		},
		jumpToKw(kw) {
			const num = parseInt(kw);
			if (!num) return;

			const date = luxon.DateTime.fromObject(
				{
					weekYear: luxon.DateTime.now().setZone(this.config.timezone).weekYear,
					weekNumber: num,
					weekday: 1,
				},
				{ zone: this.config.timezone },
			);
			this.calendarDate = date.toISODate();
		},
		handleChangeDate(newDate) {
			if (newDate && luxon.DateTime.isDateTime(newDate) && newDate.isValid)
				this.calendarDate = newDate.toISODate();
		},
		handleChangeMode(newMode, newDate) {
			if (!newMode) return;
			this.currentMode = newMode;
			if (newDate && luxon.DateTime.isDateTime(newDate) && newDate.isValid)
				this.calendarDate = newDate.toISODate();
		},
		updateCollision() {
			this.$api
				.call(ApiTempusConfig.updateCollision())
				.then(() => {
					this.$refs.config.reload();
					this.$fhcAlert.alertSuccess(this.$p.t('ui/settings_saved'));
				})
				.catch(this.$fhcAlert.handleSystemErrors);
		},
		toggleStatus(selected) {
			if (!selected || selected.length === 0) {
				this.visibleStatus = ['all'];
				return;
			}
			const hasAll = selected.includes('all');
			const hadAll = this.visibleStatus.includes('all');

			if (hasAll && !hadAll) {
				this.visibleStatus = ['all'];
				return;
			}
			this.visibleStatus = selected.filter((k) => k !== 'all');
			if (this.visibleStatus.length === 0) this.visibleStatus = ['all'];
		},
		searchfunction(params) {
			return this.$api.call(ApiSearchbar.search(params));
		},
		getPromiseFunc(start, end) {
			const hasRooms = this.rooms.length > 0;
			const hasLektoren = this.lecturers.length > 0;
			const hasStg = this.studiengaenge.length > 0;

			const filter = {};

			if (hasRooms) filter.ort = this.rooms.map((room) => room.ort_kurzbz);
			if (hasStg) {
				filter.stg = this.studiengaenge.map(
					({ stg_kz, semester, orgform_kurzbz }) => ({
						stg_kz,
						semester,
						orgform_kurzbz,
					}),
				);
			}
			if (hasLektoren)
				filter.uid = this.lecturers.map((lecture) => lecture.uid);

			if (this.previewRole === 'lektor')
				return [
					this.$api.call(
						ApiKalender.getPlanLecturer(start.toISODate(), end.toISODate()),
					),
				];

			if (this.previewRole === 'student')
				return [
					this.$api.call(
						ApiKalender.getPlanStudent(start.toISODate(), end.toISODate()),
					),
				];

			return [
				this.$api.call(
					ApiKalender.getPlan(filter, start.toISODate(), end.toISODate()),
				),
			];
		},
		toDateTime(value, timezone) {
			if (luxon.DateTime.isDateTime(value)) return value;

			if (value?.date?.isValid) return value.date;

			if (typeof value === 'number')
				return luxon.DateTime.fromMillis(value, { zone: timezone });

			if (value instanceof Date)
				return luxon.DateTime.fromJSDate(value, { zone: timezone });

			if (typeof value === 'string')
				return luxon.DateTime.fromISO(value, { zone: timezone });

			return luxon.DateTime.invalid('invalid datetime');
		},
		getLastEndOfSameDay(startDT, ends) {
			if (!ends?.length) return null;

			const dayKey = startDT.toISODate();
			let lastSameDay = null;

			for (const end of ends) {
				const dt = luxon.DateTime.isDateTime(end)
					? end
					: luxon.DateTime.fromISO(String(end), { zone: startDT.zoneName });

				if (!dt.isValid) continue;

				if (dt.toISODate() === dayKey) lastSameDay = dt;
			}

			return lastSameDay;
		},
		clampEndToGrid(startDT, durationMin, ends) {
			const calculatedEnd = startDT.plus({ minutes: durationMin });

			const lastGridEndSameDay = this.getLastEndOfSameDay(startDT, ends);

			if (!lastGridEndSameDay) return calculatedEnd;

			return calculatedEnd > lastGridEndSameDay
				? lastGridEndSameDay
				: calculatedEnd;
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
					if (onSuccess) onSuccess();
				});
		},

		resizeHandler(payload) {
			if (this.previewRole !== 'planer')
				//TODO (david) testzweck
				return;
			const { item, start, end } = payload;
			const obj = item[0];
			if (!obj?.orig?.kalender_id)
				return alert('Kein gültiges Kalender-Event zum Resizen');

			const dates = this._parseDates(start, end);

			if (!dates) return;

			return this._updateKalenderEvent(
				obj,
				dates.startDT,
				dates.endDT,
				dates.start_time,
				dates.end_time,
				() => {
					this.$refs.calendar.resetEventLoader();
					this.$refs.sidebar.reloadCoursepicker();
				},
			);
		},

		dropHandler(payload) {
			if (this.previewRole !== 'planer')
				//TODO (david) testzweck
				return;
			const { item, start, end } = payload;
			if (!item?.length) return alert('Keine Daten gedroppt');

			const obj = item[0];
			if (!obj?.type) return alert('Unbekannter Drop-Typ');

			if (payload.targetKalenderId) {
				if (obj.type !== 'lehreinheit' || !obj.orig?.lehreinheit_id)
					return alert(
						'Nur Lehreinheiten können einem Termin hinzugefügt werden',
					);

				return this.$api
					.call(
						ApiKalender.addToKalenderEvent(
							payload.targetKalenderId,
							obj.orig.lehreinheit_id,
						),
					)
					.then(() => {
						this.$refs.calendar.resetEventLoader();
						this.bcc.postMessage('dropped');
					});
			}

			const dates = this._parseDates(start, end);
			if (!dates) return;

			const { startDT, endDT, start_time, end_time } = dates;

			if (obj.type === 'reservierung') {
				this.reservierungPending = true;
				this.$refs.reservierung.show(start_time, end_time);
			} else if (obj.type === 'lehreinheit') {
				if (obj.multiweek) {
					this.openMultiWeekPreview(
						obj.orig.lehreinheit_id,
						this.ort_kurzbz ? this.ort_kurzbz : obj.orig.ort_kurzbz,
						start_time,
						end_time,
					);
					return;
				}

				return this.$api
					.call(
						ApiKalender.addKalenderEvent(
							obj.orig.lehreinheit_id,
							this.rooms.length
								? this.rooms.map((r) => r.ort_kurzbz)
								: (obj.orig.ort_kurzbz ?? []),

							start_time,
							end_time,
						),
					)
					.then((result) => result.data)
					.then((result) => {
						this.$refs.calendar.resetEventLoader();
						this.$refs.sidebar.reloadCoursepicker();
						this.bcc.postMessage('dropped');
					});
			} else if (obj.type === 'kalender') {
				return this._updateKalenderEvent(
					obj,
					startDT,
					endDT,
					start_time,
					end_time,
					() => {
						this.$refs.sidebar.unpark({
							type: obj.type,
							id: obj.orig.kalender_id,
						});
						this.$refs.calendar.resetEventLoader();
						this.bcc.postMessage('dropped');
					},
				);
			} else {
				alert('Unbekannter Drop-Typ: ' + obj.type);
			}
		},
		openMultiWeekPreview(lehreinheit_id, ort_kurzbz, start_time, end_time) {
			this.multiWeekModal.lehreinheitId = lehreinheit_id;
			this.multiWeekModal.ortKurzbz = ort_kurzbz;
			this.multiWeekModal.startTime = start_time;
			this.multiWeekModal.endTime = end_time;
			this.multiWeekModal.show = true;
		},
		closeMultiWeekModal() {
			this.multiWeekModal.show = false;
		},
		onMultiWeekConfirmed() {
			this.$refs.calendar.resetEventLoader();
			this.$refs.sidebar.reloadCoursepicker();
			this.bcc.postMessage('dropped');
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

			this.extraBackgrounds = res;
		},

		removeLecturer(uid) {
			if (uid == null) {
				for (const lect of this.lecturers) delete this.overlayCache[lect.uid];

				this.lecturers = [];
				this.$refs.calendar.resetEventLoader();
			} else {
				this.lecturers = this.lecturers.filter(
					(lecture) => lecture.uid !== uid,
				);
				delete this.overlayCache[uid];
				this.$refs.calendar.resetEventLoader();
			}
		},
		handlePreviewRoleChange(role) {
			this.previewRole = role;
			this.$refs.calendar.resetEventLoader();
		},
		triggerSync() {
			this.$api
				.call(ApiKalender.sync())
				.then(this.$refs.calendar.resetEventLoader());
		},
		onEventHover(event) {
			this.hoveredEvent = event;
		},
		onEventUnhover(event) {
			if (this.hoveredEvent?.kalender_id === event?.kalender_id) {
				this.hoveredEvent = null;
			}
		},
		parkHoveredEvent() {
			const event = this.hoveredEvent;
			if (!event?.kalender_id) return;

			if (this.$refs.sidebar.isParked(event.kalender_id)) {
				this.$refs.sidebar.unpark({ type: event.type, id: event.kalender_id });
			} else {
				this.$refs.sidebar.park(null, {
					type: 'kalender',
					id: event.kalender_id,
					orig: event,
				});
			}
		},
		deleteHoveredEvent() {
			const event = this.hoveredEvent;
			if (!event?.kalender_id) return;

			this.deleteEntry(event);
		},
		clearHoveredEvent() {
			this.hoveredEvent = null;
		},
		focusSearchbar() {
			this.$refs.header?.focusSearchbar();
		},
	},
	watch: {
		lecturers: {
			deep: true,
			handler() {
				this.rebuildExtraBackgrounds();
			},
		},
		rooms() {
			this.$refs.calendar.resetEventLoader();
		},
		studiengaenge: {
			deep: true,
			handler() {
				this.$refs.calendar.resetEventLoader();
			},
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
		this.bcc.close();
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

					if (this.renderers === null) {
						this.renderers = {};
					}
					if (!this.renderers[rendertype]) {
						this.renderers[rendertype] = {};
					}
					this.renderers[rendertype].modalTitle = modalTitle;
					this.renderers[rendertype].modalContent = modalContent;
					this.renderers[rendertype].calendarEvent = calendarEvent;
				}
			});

		this.$api.call(ApiTempusConfig.getHeader()).then((res) => {
			this.visibleStatusArray = res.data.visible_status;
			this.visibleStatus = ['all'];
		});
		this.$api.call(ApiInfo.getStudiengaenge()).then((res) => {
			this.studiengaenge_all = res.data;
		});
	},
	template: /* html */ `
	<div
		class="tempus"
		data-cy="tempus"
	>
		<keyboard-shortcuts :shortcuts="keyboardShortcuts" />
		<tempus-header
			ref="header"
			:tempus-root="tempusRoot"
			:avatar-url="avatarUrl"
			:logout-url="logoutUrl"
			:searchbaroptions="searchbaroptions"
			:searchfunction="searchfunction"
      @language-changed="this.$refs.calendar.resetEventLoader();"
		/>
		<div class="container-fluid overflow-hidden heightfull">
			<div class="row h-100">
				<tempus-app-menu />
				<tempus-sidebar-menu
					ref="sidebar"
					:preview-role="previewRole"
					:rooms="rooms"
					:studiengaenge="studiengaenge"
					:studiengaenge-all="studiengaenge_all"
					:lecturers="lecturers"
					:selected-studiensemester="selectedStudiensemester"
					@update:preview-role="handlePreviewRoleChange"
					@update:rooms="rooms = $event"
					@update:studiengaenge="studiengaenge = $event"
					@update:parked-keys="parkedKeys = $event"
					@update:selected-studiensemester="selectedStudiensemester = $event"
					@remove-lecturer="removeLecturer"
					@select-lecturer="setEmp"
					@select-kw="jumpToKw"
					@sync="triggerSync"
				/>
				<main class="col-md-8 ms-sm-auto col-lg-9 col-xl-10">
					<fhc-calendar
						ref="calendar"
						:timezone="config.timezone"
						:get-promise-func="getPromiseFunc"
						:visible-status="visibleStatus"
						:date="calendarDate"
						:mode="currentMode"
						:parkedEvents="parkedKeys"
						:visible-lecturers="visibleLecturerUids"
						@drop="dropHandler"
						@resize="resizeHandler"
						@update:date="handleChangeDate"
						@update:mode="handleChangeMode"
						@event-hover="onEventHover"
						@event-unhover="onEventUnhover"
						:extra-backgrounds="extraBackgrounds"
						@update:range="handleRange"
						class="responsive-calendar"
					/>
				</main>
			</div>
		</div>
		<app-config ref="config" v-model="appconfig" :endpoints="configEndpoints"></app-config>
		<tempus-verband-menu
			ref="verbandMenu"
			:endpoint="endpoint"
			@select-verband-and-close="onSelectVerbandAndClose"
		/>
		<raumauswahl-modal ref="raumModal" @saved="$refs.calendar.resetEventLoader()"/>
		<lehreinheit-modal ref="lehreinheitModal" @saved="$refs.calendar.resetEventLoader()"/>
		<resources-assignment-modal
		ref="resourcesAssignmentModal"
		@save-finished="$refs.calendar.resetEventLoader()"
		/>
		<tags-assignment-modal
		ref="tagsAssignmentModal"
		@tags-changed="$refs.calendar.resetEventLoader()"
		/>
		<history-modal ref="historyModal" :entries="historyEntries" />
		<reservierung
			ref="reservierung"
			:rooms="rooms"
			@saved="reservierungPending = false; $refs.calendar.resetEventLoader()"
		></reservierung>
		<multi-week-plan-modal
			:show="multiWeekModal.show"
			:lehreinheit-id="multiWeekModal.lehreinheitId"
			:ort-kurzbz="multiWeekModal.ortKurzbz"
			:start-time="multiWeekModal.startTime"
			:end-time="multiWeekModal.endTime"
			@close="closeMultiWeekModal"
			@confirmed="onMultiWeekConfirmed"
		/>
	</div>`,
};
