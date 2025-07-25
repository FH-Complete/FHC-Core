import FhcCalendar from "../../Calendar/Base.js";

import ApiLvPlan from '../../../api/factory/lvPlan.js';
import ApiAuthinfo from '../../../api/factory/authinfo.js';

import { useEventLoader } from '../../../composables/EventLoader.js';

import ModeDay from '../../Calendar/Mode/Day.js';
import ModeWeek from '../../Calendar/Mode/Week.js';
import ModeMonth from '../../Calendar/Mode/Month.js';

export const DEFAULT_MODE_LVPLAN = 'Week'

export default {
	name: 'LvPlan',
	components: {
		FhcCalendar
	},
	inject: [
		"renderers"
	],
	props: {
		viewData: Object, // NOTE(chris): this is inherited from router-view
		propsViewData: Object
	},
	data() {
		const now = luxon.DateTime.now().setZone(this.viewData.timezone);
		return {
			modes: {
				day: Vue.markRaw(ModeDay),
				week: Vue.markRaw(ModeWeek),
				month: Vue.markRaw(ModeMonth)
			},
			modeOptions: {
				day: {
					emptyMessage: Vue.computed(() => this.$p.t('lehre/noLvFound')),
					emptyMessageDetails: Vue.computed(() => this.$p.t('lehre/noLvFound'))
				},
				week: {
					collapseEmptyDays: false
				}
			},
			currentDay: this.propsViewData?.focus_date,
			calendarMode: this.propsViewData?.mode ?? DEFAULT_MODE_LVPLAN,
			studiensemester_kurzbz: null,
			studiensemester_start: null,
			studiensemester_ende: null,
			uid: null
		};
	},
	computed:{
		backgrounds() {
			let now = luxon.DateTime.now().setZone(this.viewData.timezone);

			if (this.calendarMode == 'Month')
				return [
					{
						class: 'background-past',
						end: now.startOf('day')
					}
				];

			return [
				{
					class: 'background-past',
					end: now,
					label: now.startOf('minute').toISOTime({ suppressSeconds: true, includeOffset: false })
				}
			];
		},
		downloadLinks() {
			if (!this.studiensemester_start || !this.studiensemester_ende || !this.uid)
				return false;
			
			const opts = { zone: this.viewData.timezone };
			const start = luxon.DateTime
				.fromISO(this.studiensemester_start, opts)
				.toUnixInteger();
			const ende = luxon.DateTime
				.fromISO(this.studiensemester_ende, opts)
				.toUnixInteger();

			const download_link = FHC_JS_DATA_STORAGE_OBJECT.app_root
				+ 'cis/private/lvplan/stpl_kalender.php'
				+ '?type=student'
				+ '&pers_uid=' + this.uid
				+ '&begin=' + start
				+ '&ende=' + ende;

			return [
				{ title: "excel", icon: 'fa-solid fa-file-excel', link: download_link + '&format=excel' },
				{ title: "csv", icon: 'fa-solid fa-file-csv', link: download_link + '&format=csv' },
				{ title: "ical1", icon: 'fa-regular fa-calendar', link: download_link + '&format=ical&version=1&target=ical' },
				{ title: "ical2", icon: 'fa-regular fa-calendar', link: download_link + '&format=ical&version=2&target=ical' }
			];
		}
	},
	methods: {
		eventStyle(event) {
			if (!event.farbe)
				return undefined;
			return '--event-bg:#' + event.farbe;
		},
		handleChangeDate(day) {
			const focus_date = day.toISODate();
			const mode = this.calendarMode[0].toUpperCase() + this.calendarMode.slice(1);

			this.$router.push({
				name: "LvPlan",
				params: {
					mode,
					focus_date,
					lv_id: this.propsViewData?.lv_id || null
				}
			})
			
			this.currentDay = day;
		},
		handleChangeMode(newMode) {
			const mode = newMode[0].toUpperCase() + newMode.slice(1)
			const focus_date = (this.currentDay instanceof luxon.DateTime)
				? this.currentDay.toISODate()
				: this.currentDay;
			
			this.$router.push({
				name: "LvPlan",
				params: {
					mode,
					focus_date,
					lv_id: this.propsViewData?.lv_id ?? null
				}
			});

			this.calendarMode = mode;
		},
		updateRange(rangeInterval) {
			this.rangeInterval = rangeInterval;
			this.$api
				.call(ApiLvPlan.studiensemesterDateInterval(
					this.rangeInterval.end.startOf('week').toISODate()
				))
				.then(res => {
					this.studiensemester_kurzbz = res.data.studiensemester_kurzbz;
					this.studiensemester_start = res.data.start;
					this.studiensemester_ende = res.data.ende;
				});
		}
	},
	setup(props) {
		const $api = Vue.inject('$api');

		const rangeInterval = Vue.ref(null);
		
		const { events, lv } = useEventLoader(rangeInterval, (start, end) => {
			return [
				$api.call(ApiLvPlan.LvPlanEvents(start.toISODate(), end.toISODate(), props.propsViewData.lv_id)),
				$api.call(ApiLvPlan.getLvPlanReservierungen(start.toISODate(), end.toISODate()))
			];
		});

		return {
			rangeInterval,
			events,
			lv
		};
	},
	created() {
		this.$api
			.call(ApiAuthinfo.getAuthUID())
			.then(res => {
				this.uid = res.data.uid;
			});
	},
	template:/*html*/`
	<div class="fhc-lvplan d-flex flex-column h-100" v-if="renderers">
		<h2 @click="modeOptions.week.collapseEmptyDays = !modeOptions.week.collapseEmptyDays">
			{{ $p.t('lehre/stundenplan') }}
			<span style="padding-left: 0.4em;" v-show="studiensemester_kurzbz">
				{{ studiensemester_kurzbz }}
			</span>
			<span style="padding-left: 0.5em;" v-show="propsViewData?.lv_id && lv">
				{{ $p.user_language.value === 'German' ? lv?.bezeichnung : lv?.bezeichnung_english }}
			</span>
		</h2>
		<hr>
		<fhc-calendar
			ref="calendar"
			:date="currentDay"
			:modes="modes"
			:mode-options="modeOptions"
			:mode="propsViewData.mode.toLowerCase()"
			@update:date="handleChangeDate"
			@update:mode="handleChangeMode"
			@update:range="updateRange"
			:timezone="viewData.timezone"
			:locale="$p.user_locale.value"
			show-btns
			:events="events || []"
			:backgrounds="backgrounds"
		>
			<template v-slot="{ event, mode }">
				<div
					:class="'event-type-' + event.type + ' ' + mode + 'PageContainer'"
					:type="mode == 'day' ? 'button' : undefined"
	 				:style="eventStyle(event)"
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
				<div
					v-if="downloadLinks"
					class="d-flex gap-1 justify-items-start"
				>
					<div v-for="{ title, icon, link } in downloadLinks">
						<a
							:href="link"
							:aria-label="title"
							class="py-1 px-2 m-1 btn btn-outline-secondary card"
						>
							<div class="d-flex flex-column">
								<i aria-hidden="true" :class="icon"></i>
								<span class="small">{{ title }}</span>
							</div>
						</a>
					</div>
				</div>
			</template>
		</fhc-calendar>
	</div>`
};
