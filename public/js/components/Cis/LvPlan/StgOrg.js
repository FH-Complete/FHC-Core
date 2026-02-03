import FormForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';
import FhcCalendar from "../../Calendar/LvPlan.js";

import ApiLvPlan from '../.././../api/factory/lvPlan.js';
import ApiAuthinfo from '../../../api/factory/authinfo.js';

export const DEFAULT_MODE_LVPLAN = 'Week';

export default {
	name: 'LvPlanStgOrg',
	components: {
		FormForm,
		FormInput,
		FhcCalendar,
	},
	props: {
		viewData: Object,
		propsViewData: Object
	},
	data() {
		return {
			localProps: {},
			studiensemester_kurzbz: null,
			studiensemester_start: null,
			studiensemester_ende: null,
			uid: null,
			isMitarbeiter: false,
			isStudent: false,
			currentStgBezeichnung: null,
			formData:  {
				stgkz: null,
				sem: null,
				verband: null,
				gruppe: null,
			},
			listStg: [],
			listSem: [1,2,3,4,5,6,7,8,9,10],
			listVerband: [],
			listGroup: [],
			rangeIntervalFirst: null
		};
	},
	computed: {
		maxSemester(){
			const currentStg = this.listStg.find(
				item => item.studiengang_kz === this.formData.stgkz
			);
			return currentStg.max_semester;
		},
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
		loadLvPlan(){
			if(!this.formData.stgkz){
				this.$fhcAlert.alertError(this.$p.t('LvPlan', 'chooseStg'));
				return;
			}

			if(!this.formData.sem && (this.formData.verband || this.formData.gruppe)){
				this.$fhcAlert.alertError(this.$p.t('LvPlan', 'error_SemMissing'));
				return;
			}

			if(!this.formData.verband && this.formData.gruppe){
				this.$fhcAlert.alertError(this.$p.t('LvPlan', 'error_VerbandMissing'));
				return;
			}

			const params = {
				mode: this.currentMode,
				focus_date: this.currentDay,
				stgkz: this.formData.stgkz,
				sem: this.formData.sem,
				verband: this.formData.verband,
				gruppe: this.formData.gruppe,
			};

			//ensure logic: no value after a null value in route
			if(params.sem == null)
			{
				params.verband = null;
				params.gruppe = null;
			}
			if(params.verband == null) {
				params.gruppe = null;
			}

			//delete all null values to avoid null in router
			Object.keys(params).forEach(
				key => params[key] == null && delete params[key]
			);

			this.$router.push({
				name: "StgOrgLvPlan",
				params,
			});

			this.$refs['calendar'].resetEventLoader();
		},
		loadListSem(){
			this.listSem = [...Array(this.maxSemester).keys()].map(i => i + 1);
		},
		loadListVerband(){
			this.$api
				.call(ApiLvPlan.getLehrverband(this.formData.stgkz, this.formData.semester, this.formData.verband))
				.then(result => {
					const data = result.data;
					const mappedData = data.map(item => item.verband);
					this.listVerband = [...new Set(mappedData.filter(v =>
						v !== null &&
						v !== undefined &&
						String(v).trim() !== ""
					))]
						.sort();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		loadListGroup(){
			this.$api
				.call(ApiLvPlan.getGruppe(this.formData.stgkz, this.formData.semester, this.formData.verband))
				.then(result => {
					const data = result.data;
					const mappedData = data.map(item => item.gruppe);
					this.listGroup =  [...new Set(mappedData.filter(v =>
						v !== null &&
						v !== undefined &&
						String(v).trim() !== ""))]
						.sort();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		handleChangeDate(day, newMode) {
			return this.handleChangeMode(newMode, day);
		},
		handleChangeMode(newMode, day) {
			const mode = newMode[0].toUpperCase() + newMode.slice(1);
			const focus_date = day.toISODate();

			this.$router.push({
				name: "StgOrgLvPlan",
				params: {
					mode,
					focus_date,
					stgkz: this.formData.stgkz,
					sem: this.formData.sem,
					verband: this.formData.verband,
					gruppe: this.formData.gruppe,
				},
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
				this.$api.call(ApiLvPlan.eventsStgOrg(start, end, this.formData.stgkz, this.formData.sem, this.formData.verband, this.formData.gruppe))
			];
		},
	},
	created(){
		this.$api
			.call(ApiAuthinfo.getAuthInfo())
			.then(res => {
				this.uid = res.data.uid;
				this.isMitarbeiter = res.data.isMitarbeiter;
				this.isStudent = res.data.isStudent;
			});

		this.$api
			.call(ApiLvPlan.getStudiengaenge())
			.then(result => {
				this. listStg = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		if(this.propsViewData) {
			this.formData.stgkz = this.propsViewData.stgkz ? this.propsViewData.stgkz: null;
			this.formData.sem = this.propsViewData.sem ? this.propsViewData.sem: null;
			this.formData.verband = this.propsViewData.verband ? this.propsViewData.verband: null;
			this.formData.gruppe = this.propsViewData.gruppe ? this.propsViewData.gruppe: null;
		}
	},
	template: `
	<div class="cis-lvplan-stg-org d-flex flex-column h-100">

		<div class="mt-3">
	 		<form-form class="row row-cols-1 row-cols-md-2 row-cols-lg-4 row-cols-xl-5 g-3 mb-3">
	 			<form-input
	 				type="select"
	 				v-model="formData.stgkz"
	 				@change="loadListSem(formData.stgkz)"
	 				>
					<option :value="null" selected>{{ $p.t('LvPlan/chooseStg') }}</option>
					<option
						v-for="stg in listStg"
						:key="stg.studiengang_kz"
						:value="stg.studiengang_kz"
						>
						{{ stg.kurzbzlang }} ({{ stg.bezeichnung }})
					</option>
	 			</form-input>
	 			<form-input
	 				type="select"
	 				v-model="formData.sem"
	 				@change="loadListVerband()"
	 				@click="loadListSem(formData.stgkz)"
	 				>
						<option :value="null" selected>Semester</option>
						<option
							v-for="sem in listSem"
							:key="sem"
							:value="sem"
							>
							{{ sem }}
						</option>
	 			</form-input>
	 			<form-input
	 				type="select"
	 				v-model="formData.verband"
	 				@change="loadListGroup()"
	 				>
	 					<option :value="null" selected>{{ $p.t('lehre/verband') }} </option>
					<option
						v-for="verband in listVerband"
						:key="verband"
						:value="verband"
						>
						{{ verband }}
					</option>
	 			</form-input>
	 			<form-input
	 				type="select"
	 				v-model="formData.gruppe"
	 				>
	 				<option :value="null" selected>{{ $p.t('gruppenmanagement/gruppe') }}</option>
						<option
							v-for="group in listGroup"
							:key="group"
							:value="group"
							>
							{{ group }}
						</option>
	 			</form-input>
				<button type="button" class="btn btn-secondary" @click="loadLvPlan">{{ $p.t('LvPlan/loadLvPlan') }}</button>

	 		</form-form>
	 	</div>

		<fhc-calendar
			v-show="propsViewData && propsViewData.stgkz"
			ref="calendar"
			v-model:lv="formData"
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
	</div>
	`,


};