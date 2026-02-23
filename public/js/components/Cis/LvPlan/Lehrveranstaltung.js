import FhcCalendar from "../../Calendar/LvPlan.js";

import ApiLvPlan from '../../../api/factory/lvPlan.js';
import ApiAuthinfo from '../../../api/factory/authinfo.js';

export const DEFAULT_MODE_LVPLAN = 'Week'

export default {
	name: 'LvPlanLehrveranstaltung',
	components: {
		FhcCalendar
	},
	props: {
		viewData: Object, // NOTE(chris): this is inherited from router-view
		propsViewData: Object
	},
	data() {
		return {
			lv: null
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
		currentLv() {
			if (isNaN(parseInt(this.propsViewData?.lv_id)))
				return null;
			return this.propsViewData.lv_id;
		},
		lvTitle() {
			if (this.currentLv === null)
				return '';
			if (!this.lv)
				return '';

			if (this.$p.user_language.value === 'English')
				return this.lv.bezeichnung_english;

			return this.lv.bezeichnung;
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
					lv_id: this.currentLv
				}
			});
		},
		getPromiseFunc(start, end) {
			return [
				this.$api.call(ApiLvPlan.eventsLv(this.propsViewData.lv_id, start.toISODate(), end.toISODate())),
				this.$api.call(ApiLvPlan.getLvPlanReservierungen(start.toISODate(), end.toISODate()))
			];
		}
	},
	created() {
		if (this.currentLv === null)
			return;
		this.$api
			.call(ApiLvPlan.getLv(this.propsViewData?.lv_id))
			.then(res => {
				this.lv = res.data;
			});
	},
	template: /*html*/`
	<div class="cis-lvplan-personal d-flex flex-column h-100">
		<h2>
			{{ $p.t('lehre/stundenplan') }}
			<span v-if="lvTitle" class="ps-3">
				{{ lvTitle }}
			</span>
		</h2>
		<hr>
		<div v-if="currentLv === null || lv === false">
			{{ $p.t('lehre/noLvFound') }}
		</div>
		<fhc-calendar
			v-else-if="lv"
			ref="calendar"
			:timezone="viewData.timezone"
			:get-promise-func="getPromiseFunc"
			:date="currentDay"
			:mode="currentMode"
			@update:date="handleChangeDate"
			@update:mode="handleChangeMode"
			class="responsive-calendar"
		/>
	</div>`
};
