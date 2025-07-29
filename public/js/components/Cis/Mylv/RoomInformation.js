import FhcCalendar from "../../Calendar/Base.js";

import ApiLvPlan from '../../../api/factory/lvPlan.js';

import { useEventLoader } from '../../../composables/EventLoader.js';

import ModeDay from '../../Calendar/Mode/Day.js';
import ModeWeek from '../../Calendar/Mode/Week.js';
import ModeMonth from '../../Calendar/Mode/Month.js';

export const DEFAULT_MODE_RAUMINFO = 'Week'

export default {
	name: "RoomInformation",
	components: {
		FhcCalendar
	},
	inject: [
		"renderers"
	],
	props:{
		viewData: Object, // NOTE(chris): this is inherited from router-view
		propsViewData: Object
	},
	data() {
		return {
			modes: {
				day: Vue.markRaw(ModeDay),
				week: Vue.markRaw(ModeWeek),
				month: Vue.markRaw(ModeMonth)
			},
			modeOptions: {
				day: {
					emptyMessage: Vue.computed(() => this.$p.t('rauminfo/keineRaumReservierung')),
					emptyMessageDetails: Vue.computed(() => this.$p.t('rauminfo/keineRaumReservierung'))
				},
				week: {
					collapseEmptyDays: false
				}
			},
			teachingunits: null
		}
	},
	computed: {
		currentDay() {
			return this.propsViewData?.focus_date || luxon.DateTime.now().setZone(this.viewData.timezone).toISODate();
		},
		currentMode() {
			return this.propsViewData?.mode || DEFAULT_MODE_RAUMINFO;
		},
		backgrounds() {
			let now = luxon.DateTime.now().setZone(this.viewData.timezone);

			if (this.currentMode == 'Month')
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
		}
	},
	methods:{
		eventStyle(event) {
			if (!event.farbe)
				return undefined;
			return '--event-bg:#' + event.farbe;
		},
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
		updateRange(rangeInterval) {
			this.rangeInterval = rangeInterval;
		}
	},
	setup(props) {
		const $api = Vue.inject('$api');

		const rangeInterval = Vue.ref(null);
		
		const { events } = useEventLoader(rangeInterval, (start, end) => {
			return [
				$api.call(ApiLvPlan.getRoomInfo(props.propsViewData.ort_kurzbz, start.toISODate(), end.toISODate())),
				$api.call(ApiLvPlan.getOrtReservierungen(props.propsViewData.ort_kurzbz, start.toISODate(), end.toISODate()))
			];
		});

		return {
			rangeInterval,
			events
		};
	},
	created() {
		this.$api
			.call(ApiLvPlan.getStunden())
			.then(res => {
				return this.teachingunits = res.data.map(el => ({
					id: el.stunde,
					start: el.beginn,
					end: el.ende
				}));
			});
	},
	template: /*html*/`
	<div class="fhc-roominformation d-flex flex-column h-100">
		<h2>{{ $p.t('rauminfo/rauminfo') }} {{ propsViewData.ort_kurzbz }}</h2>
		<hr>
		<fhc-calendar 
			ref="calendar"
			class="responsive-calendar"
			:date="currentDay"
			:modes="modes"
			:mode-options="modeOptions"
			:mode="currentMode"
			@update:date="handleChangeDate"
			@update:mode="handleChangeMode"
			@update:range="updateRange"
			:timezone="viewData.timezone"
			:locale="$p.user_locale.value"
			show-btns
			:events="events || []"
			:backgrounds="backgrounds"
			:time-grid="teachingunits"
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
		</fhc-calendar>
	</div>`
};
