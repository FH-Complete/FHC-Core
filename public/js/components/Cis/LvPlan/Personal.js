import FhcCalendar from "../../Calendar/LvPlan.js";

import ApiLvPlan from '../../../api/factory/lvPlan.js';
import ApiAuthinfo from '../../../api/factory/authinfo.js';

export const DEFAULT_MODE_LVPLAN = 'Week'

export default {
	name: 'LvPlanPersonal',
	components: {
		FhcCalendar
	},
	props: {
		viewData: Object, // NOTE(chris): this is inherited from router-view
		propsViewData: Object
	},
	data() {
		return {
			studiensemester_kurzbz: null,
			studiensemester_start: null,
			studiensemester_ende: null,
			uid: null,
			isMitarbeiter: false,
			isStudent: false
		};
	},
	computed:{
		currentDay() {
			if (!this.propsViewData?.focus_date || isNaN(new Date(this.propsViewData?.focus_date)))
				return luxon.DateTime.now().setZone(this.viewData.timezone).toISODate();
			return this.propsViewData?.focus_date;
		},
		currentMode() {
			if (!this.propsViewData?.mode || !['day', 'week', 'month'].includes(this.propsViewData?.mode.toLowerCase()))
				return DEFAULT_MODE_LVPLAN;
			return this.propsViewData?.mode;
		},
		downloadLinks() {
			if (!this.studiensemester_start || !this.studiensemester_ende || !this.uid)
				return false;

			let type = false;
			type = this.isStudent ? 'student' : type;
			type = this.isMitarbeiter ? 'lektor' : type;
			if (false === type)
			{
				return;
			}

			const opts = { zone: this.viewData.timezone };
			const start = luxon.DateTime
				.fromISO(this.studiensemester_start, opts)
				.toUnixInteger();
			const ende = luxon.DateTime
				.fromISO(this.studiensemester_ende, opts)
				.toUnixInteger();

			const download_link = FHC_JS_DATA_STORAGE_OBJECT.app_root
				+ 'cis/private/lvplan/stpl_kalender.php'
				+ '?type=' + type
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
				name: "MyLvPlan",
				params: {
					mode,
					focus_date
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
				this.$api.call(ApiLvPlan.eventsPersonal(start.toISODate(), end.toISODate())),
				this.$api.call(ApiLvPlan.getLvPlanReservierungen(start.toISODate(), end.toISODate()))
			];
		}
	},
	created() {
		this.$api
			.call(ApiAuthinfo.getAuthInfo())
			.then(res => {
				this.uid = res.data.uid;
				this.isMitarbeiter = res.data.isMitarbeiter;
				this.isStudent = res.data.isStudent;
			});
	},
	template: /*html*/`
	<div class="cis-lvplan-personal d-flex flex-column h-100">
		<h2>
			{{ $p.t('lehre/stundenplan') }}
			<span v-if="studiensemester_kurzbz" class="ps-3">
				{{ studiensemester_kurzbz }}
			</span>
		</h2>
		<hr>
		<fhc-calendar
			ref="calendar"
			:timezone="viewData.timezone"
			:get-promise-func="getPromiseFunc"
			:date="currentDay"
			:mode="currentMode"
			@update:date="handleChangeDate"
			@update:mode="handleChangeMode"
			@update:range="updateRange"
			class="responsive-calendar"
		>
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
		</fhc-calendar>
	</div>`
};
