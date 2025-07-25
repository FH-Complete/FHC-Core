import FhcCalendar from "../../Calendar/Base.js";

import ApiStudenplan from '../../../api/factory/lvPlan.js';

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
			currentDay: this.propsViewData?.focus_date,
			calendarMode: this.propsViewData?.mode ?? DEFAULT_MODE_RAUMINFO
		}
	},
	computed: {
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
		}
	},
	methods:{
		eventStyle(event) {
			if (!event.farbe)
				return undefined;
			return '--event-bg:#' + event.farbe;
		},
		handleChangeDate(day) {
			const focus_date = day.toISODate();
			const mode = this.calendarMode[0].toUpperCase() + this.calendarMode.slice(1);

			this.$router.push({
				name: "RoomInformation",
				params: {
					mode,
					focus_date,
					ort_kurzbz: this.propsViewData.ort_kurzbz
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
				name: "RoomInformation",
				params: {
					mode,
					focus_date,
					ort_kurzbz: this.propsViewData.ort_kurzbz
				}
			})

			this.calendarMode = mode
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
				$api.call(ApiStudenplan.getRoomInfo(props.propsViewData.ort_kurzbz, start.toISODate(), end.toISODate())),
				$api.call(ApiStudenplan.getOrtReservierungen(props.propsViewData.ort_kurzbz, start.toISODate(), end.toISODate()))
			];
		});

		return {
			rangeInterval,
			events
		};
	},
	template: /*html*/`
	<div class="fhc-roominformation d-flex flex-column h-100">
		<h2>{{ $p.t('rauminfo/rauminfo') }} {{ propsViewData.ort_kurzbz }}</h2>
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
				<div
					v-else-if="mode == 'month'"
					:class="'event-type-' + event.type"
	 				:style="eventStyle(event)"
					class="d-flex flex-column align-items-center justify-content-evenly h-100"
				>
					<span>{{ event?.topic }}</span>
				</div>
				<div
					v-else-if="mode == 'week'"
					:class="'event-type-' + event.type"
	 				:style="eventStyle(event)"
					class="border border-secondary d-flex flex-column align-items-center justify-content-evenly h-100"
					type="button"
				>
					<span>{{ event?.topic }}</span>
					<span v-for="lektor in event?.lektor">{{ lektor.kurzbz }}</span>
					<span>{{ event?.ort_kurzbz }}</span>
				</div>
				<div
					v-else-if="mode == 'day'"
					:class="'event-type-' + event.type"
	 				:style="eventStyle(event)"
					type="button"
					class="border border-secondary d-flex align-items-center justify-content-center text-center h-100"
				>
					<div class="col ">
						<p>{{ $p.t('lehre/lehrveranstaltung') }}:</p>
						<p class="m-0">
							{{ event?.topic }}
						</p>
					</div>
					<div class="col ">
						<p>{{ $p.t('lehre/lektor') }}:</p>
						<p class="m-0" v-for="lektor in event?.lektor">
							{{ lektor.kurzbz }}
						</p>
					</div>
					<div class="col ">
						<p>{{ $p.t('profil/Ort') }}: </p>
						<p class="m-0">
							{{ event?.ort_kurzbz }}
						</p>
					</div>
				</div>
			</template>
		</fhc-calendar>
	</div>`
};
