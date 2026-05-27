import FhcCalendar from "../../Calendar/LvPlan.js";

import ApiLvPlan from '../../../api/factory/lvPlan.js';

export const DEFAULT_MODE_RAUMINFO = 'Week'

export default {
	name: "RoomInformation",
	components: {
		FhcCalendar
	},
	props:{
		viewData: Object, // NOTE(chris): this is inherited from router-view
		propsViewData: Object
	},
	computed: {
		currentDay() {
			return this.propsViewData?.focus_date || luxon.DateTime.now().setZone(this.viewData.timezone).toISODate();
		},
		currentMode() {
			return this.propsViewData?.mode || DEFAULT_MODE_RAUMINFO;
		}
	},
	methods:{
		handleChangeDate(day, newMode) {
			return this.handleChangeMode(newMode, day);
		},
		handleChangeMode(newMode, day) {
			const mode = newMode[0].toUpperCase() + newMode.slice(1)
			const focus_date = day.toISODate();

			this.$router.push({
				name: "RoomInformation",
				params: {
					mode,
					focus_date,
					ort_kurzbz: this.propsViewData.ort_kurzbz
				}
			});
		},
		getPromiseFunc(start, end) {
			return [
				this.$api.call(ApiLvPlan.getRoomInfo(this.propsViewData.ort_kurzbz, start.toISODate(), end.toISODate())),
				this.$api.call(ApiLvPlan.getOrtReservierungen(this.propsViewData.ort_kurzbz, start.toISODate(), end.toISODate()))
			];
		}
	},
	template: /*html*/`
	<div class="fhc-roominformation d-flex flex-column h-100">
		<h2>{{ $p.t('rauminfo/rauminfo') }} {{ propsViewData.ort_kurzbz }}</h2>
		<hr>
		<fhc-calendar 
			ref="calendar"
			:timezone="viewData.timezone"
			:get-promise-func="getPromiseFunc"
			:date="currentDay"
			:mode="currentMode"
			@update:date="handleChangeDate"
			@update:mode="handleChangeMode"
			class="responsive-calendar"
		></fhc-calendar>
	</div>`
};
