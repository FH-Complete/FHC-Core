import FhcCalendar from "./Base.js";

import ApiLvPlan from '../../api/factory/lvPlan.js';

import { useEventLoader } from '../../composables/EventLoader.js';
import { useRenderers } from '../../composables/Renderers.js';

import ModeDay from './Mode/Day.js';
import ModeWeek from './Mode/Week.js';
import ModeMonth from './Mode/Month.js';
import ModeList from './Mode/List.js';

export default {
	name: "CalendarLvPlan",
	components: {
		FhcCalendar
	},
	inject: ["isMobile"],
	props: {
		date: {
			type: [Date, String, Number, luxon.DateTime],
			default: luxon.DateTime.local()
		},
		mode: {
			type: String,
			default: 'Week'
		},
		getPromiseFunc: {
			type: Function,
			required: true
		},
		reservierbar: {
			type: Boolean,
			default: false
		},
		createContext: {
			type: Object,
			default: () => ({})
		},
	},
	provide() {
		return {
			shouldCompactEvents: Vue.computed(
				() => this.$props.mode === "Month" && this.isMobile,
			),
			compactibleEventTypes: Vue.computed(
				() => this.compactibleEventTypes,
			),
		};
	},
	emits: [
		"update:date",
		"update:mode",
		"update:range",
		"create-event",
		"delete-event",
		'update:reservierbarMap'
	],
	data() {
		return {
			timezone: FHC_JS_DATA_STORAGE_OBJECT.timezone,
			isReservierbar: Vue.computed(() => {
				if (!this.reservierbar)
					return false;

				if (!this.reservierbarMap)
					return false;

				if (typeof this.reservierbarMap === 'object')
					return Object.keys(this.reservierbarMap).length > 0;

				return false;
			}),
			modeOptions: {
				day: {
					emptyMessage: Vue.computed(() => this.$p.t('lehre/noLvFound')),
					emptyMessageDetails: Vue.computed(() => this.$p.t('lehre/noLvFound'))
				},
				week: {
					collapseEmptyDays: false
				},
				list: {
					length: 7,
				},
			},
			teachingunits: null,
			compactibleEventTypes: [],
		};
	},
	computed: {
		backgrounds() {
			let now = luxon.DateTime.now().setZone(this.timezone);

			if (this.mode == 'Month')
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
		modes() {
			let modes = {
				day: Vue.markRaw(ModeDay),
				month: Vue.markRaw(ModeMonth),
			};
			if (this.isMobile) {
				modes.list = Vue.markRaw(ModeList);
			} else {
				modes.week = Vue.markRaw(ModeWeek);
			}

			return modes;
		},
	},
	methods: {
		eventStyle(event) {
			if (!event.farbe)
				return undefined;
			return '--event-bg:#' + event.farbe;
		},
		updateRange(rangeInterval) {
			this.rangeInterval = rangeInterval;
			this.$emit('update:range', rangeInterval);
		},
		resetEventLoader() {
			this.reset();
		},
		async getStunden() {
			let stundenResponse = await this.$api.call(ApiLvPlan.getStunden());
			this.teachingunits = stundenResponse.data.map((el) => ({
				id: el.stunde,
				start: el.beginn,
				end: el.ende,
			}));
		},
		async getCompactibleEventTypes() {
			let compactibleEventTypesResponse = await this.$api.call(
				ApiLvPlan.getCompactibleEventTypes(),
			);
			this.compactibleEventTypes = compactibleEventTypesResponse.data;
		},
		closeModal() {
			this.$refs.calendar.hideEventModal();
		},
	},
	setup(props, context) {
		const rangeInterval = Vue.ref(null);
		
		const { events, lv, reservierbarMap, reset  } = useEventLoader(rangeInterval, props.getPromiseFunc);

		Vue.watch(lv, newValue => {
			context.emit('update:lv', newValue);
		});

		const { renderers } = useRenderers();

		Vue.watch(reservierbarMap, newVal => {
			context.emit('update:reservierbarMap', newVal);
		});

		return {
			rangeInterval,
			events,
			lv,
			reservierbarMap,
			reset,
			renderers
		};
	},
	async created() {
		await this.getStunden();
		await this.getCompactibleEventTypes();
	},
	template: /* html */`
	<fhc-calendar
		ref="calendar"
		class="fhc-calendar-lvplan"
		:date="date"
		:modes="modes"
		:mode-options="modeOptions"
		:mode="mode"
		:timezone="timezone"
		:locale="$p.user_locale.value"
		:events="events || []"
		:backgrounds="backgrounds"
		:time-grid="teachingunits"
		:reservierbar-map="reservierbarMap"
		:isReservierbar="isReservierbar"
		:create-context="createContext"
		show-btns
		@update:date="(newDate, newMode) => $emit('update:date', newDate, newMode)"
		@update:mode="(newMode, newDate) => $emit('update:mode', newMode, newDate)"
		@update:range="updateRange"
	>
		<template v-slot="{ event, mode }">
			<div
				v-if="!event"
				class="h-100 d-flex justify-content-center align-items-center"
			>
				{{ $p.t('lehre/noLvFound') }}
			</div>
			<div
				v-else
				:class="'event-type-' + event.type + ' ' + mode + 'PageContainer'"
				:type="mode == 'day' ? 'button' : undefined"
 				:style="eventStyle(event)"
			>
				<component
					v-if="mode == 'event'"
					:is="renderers[event.type]?.modalContent"
					:event="event"
					@create-event="(event) => $emit('create-event', event)"
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
					@delete-event="(event) => $emit('delete-event', event)"
					:timeSlotDisplayBehavior="
						$props.mode.toLowerCase() === 'list' ? 'always' : 'default'
					"
				></component>
			</div>
		</template>
		<template #actions>
			<slot />
		</template>
	</fhc-calendar>`
}
