import FhcCalendar from "./Base.js";

import ApiLvPlan from '../../api/factory/lvPlan.js';

import { useEventLoader } from '../../composables/EventLoader.js';
import { useRenderers } from '../../composables/Renderers.js';

import ModeDay from './Mode/Day.js';
import ModeWeek from './Mode/Week.js';
import ModeMonth from './Mode/Month.js';
import ModeList from './Mode/List.js';
import ModeRange from './Mode/Range.js';

export default {
	name: "CalendarLvPlan",
	components: {
		FhcCalendar
	},
	inject: {
		isMobile: {
			default: false,
		},
		rangeLength: {
			default: 30,
		}
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
		"update:range"
	],
	data() {
		return {
			timezone: FHC_JS_DATA_STORAGE_OBJECT.timezone,
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

			if (this.mode == 'Month') {
				return [
					{
						class: 'background-past',
						end: now.startOf('day')
					}
				];
			} else if (this.mode == 'Range') {
				// todo: remove
				return [];
				const start = luxon.DateTime.fromISO(this.$props.date);
				const end = start.plus({days: this.rangeLength});
				let backgrounds = [];

				let saturdayOffset = 6 - start.weekday;
				let saturday = start.plus({days: saturdayOffset});
				while (saturday.ts < end.ts) {
					backgrounds.push(
						{
							class: "background-weekend",
							start: saturday.startOf("day"),
							end: saturday.plus({days: 1}).endOf("day"),
						}
					);

					saturday = saturday.plus({days: 7});
				}

				return backgrounds;
			} else {
				return [
					{
						class: 'background-past',
						end: now,
						label: now.startOf('minute').toISOTime({ suppressSeconds: true, includeOffset: false })
					}
				];
			}
		},
		modes() {
			let modes = {
				day: Vue.markRaw(ModeDay),
				month: Vue.markRaw(ModeMonth),
			};
			if (!this.isMobile) {
				modes.week = Vue.markRaw(ModeWeek);
				modes.range = Vue.markRaw(ModeRange);
			} else {
				modes.list = Vue.markRaw(ModeList);
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
	},
	setup(props, context) {
		const rangeInterval = Vue.ref(null);
		
		const { events, lv, reset } = useEventLoader(rangeInterval, props.getPromiseFunc);

		Vue.watch(lv, newValue => {
			context.emit('update:lv', newValue);
		});

		const { renderers } = useRenderers();

		return {
			rangeInterval,
			events,
			lv,
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
		show-btns
		@update:date="(newDate, newMode, newRangeLength) => $emit('update:date', newDate, newMode, newRangeLength)"
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
			<div v-else-if="!renderers || !renderers[event.type]" class="placeholder-glow">
				<span class="placeholder col-12"></span>
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
