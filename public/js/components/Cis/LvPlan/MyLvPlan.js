import FhcCalendar from "../../Calendar/LvPlan.js";

import ApiLvPlan from '../../../api/factory/lvPlan.js';
import ApiStudienSemester from '../../../api/factory/studiensemester.js';
import ApiAuthinfo from '../../../api/factory/authinfo.js';

export const DEFAULT_MODE_LVPLAN_DESKTOP = "Week";
export const DEFAULT_MODE_LVPLAN_MOBILE = "List";

export default {
	name: 'LvPlanPersonal',
	components: {
		FhcCalendar
	},
	props: {
		propsViewData: Object
	},
	data() {
		return {
			uid: null,
			isMitarbeiter: false,
			isStudent: false,
			timezone: FHC_JS_DATA_STORAGE_OBJECT.timezone,
			startSemester: null,
			endSemester: null,
		};
	},
	inject: ["isMobile"],
	computed:{
		currentDay() {
			if (!this.propsViewData?.focus_date || isNaN(new Date(this.propsViewData?.focus_date)))
				return luxon.DateTime.now().setZone(this.timezone).toISODate();
			return this.propsViewData?.focus_date;
		},
		currentMode() {
			let validModes = ["day", "month"];
			validModes.push(this.isMobile ? "list" : "week");

			const defaultMode = this.isMobile
				? DEFAULT_MODE_LVPLAN_MOBILE
				: DEFAULT_MODE_LVPLAN_DESKTOP;

			if (
				!this.propsViewData?.mode ||
				!validModes.includes(this.propsViewData?.mode.toLowerCase())
			)
				return defaultMode;
			return this.propsViewData?.mode;
		},
		downloadLinks() {
			const startDate = this.startSemester?.start;
			const endDate = this.endSemester ? this.endSemester.ende : this.startSemester?.ende;

			if (!startDate || !endDate || !this.uid)
				return false;
			
			let type = false;
			type = this.isStudent ? 'student' : type;
			type = this.isMitarbeiter ? 'lektor' : type;
			if (false === type)
			{
				return;
			}

			const opts = { zone: this.timezone };
			const start = luxon.DateTime
				.fromISO(startDate, opts)
				.toUnixInteger();
			const ende = luxon.DateTime
				.fromISO(endDate, opts)
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
	watch: {
		async isMobile() {
			await this.$nextTick();
			this.handleChangeMode(
				this.currentMode,
				luxon.DateTime.fromISO(this.currentDay, {
					zone: this.timezone,
				}),
			);
		},
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
		async updateRange(rangeInterval) {
			const semesterResponse = await this.$api.call(
				ApiStudienSemester.getContainingOrNearestByDateRange(
					rangeInterval.s.toISO().slice(0, 10),
					rangeInterval.e.toISO().slice(0, 10),
				),
			);
			this.startSemester = 
				semesterResponse.data.length
					? semesterResponse.data[0]
					: null;
			this.endSemester =
				semesterResponse.data.length > 1
					? semesterResponse.data[1]
					: null;
		},
		getPromiseFunc(start, end) {
			return [
				this.$api.call(ApiLvPlan.eventsPersonal(start.toISODate(), end.toISODate())),
				this.$api.call(ApiLvPlan.getLvPlanReservierungen(start.toISODate(), end.toISODate()))
			];
		},
		async fetchAuthInfo() {
			const authInfoResponse = await this.$api.call(ApiAuthinfo.getAuthInfo());
			
			const authInfo = authInfoResponse.data;
			this.uid = authInfo.uid;
			this.isMitarbeiter = authInfo.isMitarbeiter;
			this.isStudent = authInfo.isStudent;
		},
	},
	async created() {
		await this.fetchAuthInfo();
	},
	template: /*html*/`
	<div class="cis-lvplan-personal d-flex flex-column h-100">
		<h2>
			{{ $p.t('lehre/stundenplan') }}
			<span v-if="startSemester?.studiensemester_kurzbz">
				{{ startSemester.studiensemester_kurzbz }}
				<span v-if="endSemester?.studiensemester_kurzbz">
					{{ "- " + endSemester.studiensemester_kurzbz }}
				</span>
			</span>
		</h2>
		<hr>
		<fhc-calendar
			v-if="timezone"
			ref="calendar"
			:timezone="timezone"
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
