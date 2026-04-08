import FhcCalendar from "./Base.js";

import ApiLvPlan from '../../api/factory/lvPlan.js';

import { useEventLoader } from '../../composables/EventLoader.js';
import { useRenderers } from '../../composables/Renderers.js';

import ModeDay from './Mode/Day.js';
import ModeWeek from './Mode/Week.js';
import ModeMonth from './Mode/Month.js';

export default {
	name: "CalendarLvPlan",
	components: {
		FhcCalendar
	},
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
		}
	},
	emits: [
		"update:date",
		"update:mode",
		"update:range"
	],
	data() {
		return {
			timezone: FHC_JS_DATA_STORAGE_OBJECT.timezone,
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
			teachingunits: null
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
		}
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
		}
	},
	setup(props, context) {
		const rangeInterval = Vue.ref(null);
		
		const { events, lv } = useEventLoader(rangeInterval, props.getPromiseFunc);

		Vue.watch(lv, newValue => {
			context.emit('update:lv', newValue);
		});

		const { renderers } = useRenderers();

		return {
			rangeInterval,
			events,
			lv,
			renderers
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
		show-btns
		@update:date="(newDate, newMode) => $emit('update:date', newDate, newMode)"
		@update:mode="(newMode, newDate) => $emit('update:mode', newMode, newDate)"
		@update:range="updateRange"
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
			<slot />
		</template>
	</fhc-calendar>`
}
