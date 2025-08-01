import FhcCalendar from "../../Calendar/LvPlan.js";

import ApiLvPlan from '../../../api/factory/lvPlan.js';
import ApiAuthinfo from '../../../api/factory/authinfo.js';

export const DEFAULT_MODE_LVPLAN = 'Week'

export default {
	name: 'LvPlan',
	components: {
		FhcCalendar
	},
	props: {
		viewData: Object, // NOTE(chris): this is inherited from router-view
		propsViewData: Object
	},
	data() {
		const now = luxon.DateTime.now().setZone(this.viewData.timezone);
		return {
			studiensemester_kurzbz: null,
			studiensemester_start: null,
			studiensemester_ende: null,
			uid: null,
			lv: null
		};
	},
	computed:{
		currentDay() {
			return this.propsViewData?.focus_date || luxon.DateTime.now().setZone(this.viewData.timezone).toISODate();
		},
		currentMode() {
			return this.propsViewData?.mode || DEFAULT_MODE_LVPLAN;
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
		handleChangeDate(day, newMode) {
			return this.handleChangeMode(newMode, day);
		},
		handleChangeMode(newMode, day) {
			const mode = newMode[0].toUpperCase() + newMode.slice(1)
			const focus_date = day.toISODate();
			
			this.$router.push({
				name: "LvPlan",
				params: {
					mode,
					focus_date,
					lv_id: this.propsViewData?.lv_id ?? null
				}
			});
		},
		updateRange(rangeInterval) {
			this.$api
				.call(ApiLvPlan.studiensemesterDateInterval(
					rangeInterval.end.startOf('week').toISODate()
				))
				.then(res => {
					this.studiensemester_kurzbz = res.data.studiensemester_kurzbz;
					this.studiensemester_start = res.data.start;
					this.studiensemester_ende = res.data.ende;
				});
		},
		getPromiseFunc(start, end) {
			return [
				this.$api.call(ApiLvPlan.LvPlanEvents(start.toISODate(), end.toISODate(), this.propsViewData.lv_id)),
				this.$api.call(ApiLvPlan.getLvPlanReservierungen(start.toISODate(), end.toISODate()))
			];
		}
	},
	created() {
		this.$api
			.call(ApiAuthinfo.getAuthUID())
			.then(res => {
				this.uid = res.data.uid;
			});
	},
	template: /*html*/`
	<div class="cis-lvplan-personal d-flex flex-column h-100">
		<h2>
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
			v-model:lv="lv"
			:timezone="viewData.timezone"
			:get-promise-func="getPromiseFunc"
			:date="currentDay"
			:mode="currentMode"
			@update:date="handleChangeDate"
			@update:mode="handleChangeMode"
			@update:range="updateRange"
			class="responsive-calendar"
		>
			<template>
				<div
					v-if="downloadLinks"
					class="d-flex gap-1 justify-items-start"
				>
					<div v-for="{ title, icon, link } in downloadLinks">
						<a
							:href="link"
							:aria-label="title"
							class="py-1 btn btn-outline-secondary"
						>
							<div class="d-flex flex-column">
								<i aria-hidden="true" :class="icon"></i>
								<span style="font-size:.5rem">{{ title }}</span>
							</div>
						</a>
					</div>
				</div>
			</template>
		</fhc-calendar>
	</div>`
};
