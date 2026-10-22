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

	if (!start.isValid || !end.isValid || end < start) return null;

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
		cisRoot: String,
	},
	provide() {
		return {
			cisRoot: this.cisRoot,
			defaultSemester: this.defaultSemester,
			currentSemester: this.defaultSemester,
			renderers: Vue.computed(() => this.renderers),
			appConfig: Vue.computed(() => this.appconfig),
			canToggleGrid: this.permissions.stundenraster,
			isHeaderSticky: true,
			isInitialColumnWidthSyncPostponed: true,
		};
	},
	data() {
		const today = luxon.DateTime.now()
			.setZone(this.config.timezone)
			.toISODate();

		return {
			appconfig: {},
			renderers: {},
			urlRange: null,
			planFilter: {},
			calendarDate: today,
			previewRole: 'planer',
		};
	},
	methods: {
		handleChangeDate(newDate) {
			if (!(newDate && luxon.DateTime.isDateTime(newDate) && newDate.isValid))
				return;

			this.calendarDate = newDate.toISODate();
		},
		getPromiseFunc(start, end) {
			const startDate = start.toISODate();
			const endDate = end.toISODate();
			const collisionCheck = true;

			if (this.previewRole === 'lektor') {
				return [
					this.$api.call(
						ApiKalender.getPlanLecturer(
							startDate,
							endDate,
							collisionCheck,
						),
					),
				];
			}

			if (this.previewRole === 'student') {
				return [
					this.$api.call(
						ApiKalender.getPlanStudent(
							startDate,
							endDate,
							collisionCheck,
						),
					),
				];
			}

			return [
				this.$api.call(
					ApiKalender.getPlan(
						this.planFilter,
						startDate,
						endDate,
						collisionCheck,
					),
				),
			];
		},
	},
	watch: {
		'$route.query': {
			immediate: true,
			handler(query) {
				this.planFilter = getPlanFilterFromQuery(query);
				this.previewRole = Array.isArray(query?.previewRole)
					? query.previewRole[0]
					: (query?.previewRole ?? 'planer');
				this.urlRange = getRangeFromQuery(query, this.config.timezone);
				const rangeStart =
					this.urlRange?.start.toISODate() ??
					luxon.DateTime.now().setZone(this.config.timezone).toISODate();

				this.calendarDate = rangeStart;
			},
		},
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
			:date="calendarDate"
			:range="urlRange"
			:mode="'range'"
			:modes="['range']"
			:is-event-dragging-enabled="false"
			:is-event-resizing-enabled="false"
			:isRangeVirtualScrollEnabled="false"
			:can-access-settings-view="false"
			:can-toggle-collision-check="false"
			@update:date="handleChangeDate"
			class="responsive-calendar"
			:cache-multiplier="0"
		/>
	</div>`,
};
