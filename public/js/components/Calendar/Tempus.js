import FhcCalendar from "./Base.js";

import ApiLvPlan from '../../api/factory/lvPlan.js';

import { useEventLoader } from '../../composables/EventLoader.js';

import ModeDay from './Mode/Day.js';
import ModeWeek from './Mode/Week.js';
import ModeMonth from './Mode/Month.js';
import ModeTable from './Mode/Table.js';
import ApiTempusConfig  from '../../api/factory/tempus/config.js';
import ApiKalender from '../../api/factory/tempus/kalender.js';


export default {
	name: "CalendarTempus",
	components: {
		FhcCalendar
	},
	inject: {
		renderers: {from: 'renderers'},
		appConfig: {
			from: 'appConfig',
			default: {
				visible_status: 'all'
			}
		}
	},
	props: {
		timezone: {
			type: String,
			required: true
		},
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
		parkedEvents: {
			type: Object,
			default: () => new Set()
		},
		visibleLecturers: {
			type: Array,
			default: null
		},
		extraBackgrounds: {
			type: Array,
			default: () => []
		}
	},
	emits: [
		"update:date",
		"update:mode",
		"update:range",
		"drop"
	],

	data() {
		return {
			modes: {
				week: Vue.markRaw(ModeWeek),
				month: Vue.markRaw(ModeMonth),
				tableList: Vue.markRaw(ModeTable),
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
			teachingunits: null,
			visibleStatusArray: [],
			visibleStatus: []
		};
	},
	computed: {
		backgrounds() {
			let now = luxon.DateTime.now().setZone(this.timezone);

			let past = [];
			if (this.mode == 'Month')
			{
				past = [{
					class: 'background-past',
					end: now.startOf('day')
				}];
			}
			else
			{
				past = [{
					class: 'background-past',
					end: now,
					label: now.startOf('minute').toISOTime({ suppressSeconds: true, includeOffset: false })
				}];
			}

			return [
				...past,
				...(this.extraBackgrounds || [])
			];
		},
		visibleEvents()
		{
			let list = this.events;

			if (Array.isArray(this.visibleLecturers))
			{
				const visibleLectures = new Set(this.visibleLecturers);

				list = list.filter(event => {
					if (!event.lektor?.length)
						return true;
					return event.lektor.some(lektor => visibleLectures.has(lektor.mitarbeiter_uid));
				});
			}

			if (!this.visibleStatus.length || this.visibleStatus.includes('all'))
				return list;

			return list.filter(event => this.visibleStatus.includes(event.status_kurzbz));
		},
	},
	methods: {
		toggleStatus(status) {
			if (status === 'all')
			{
				this.visibleStatus = ['all'];
				return;
			}

			this.visibleStatus = this.visibleStatus.filter(visibleStatus => visibleStatus !== 'all');

			let found = this.visibleStatus.indexOf(status);

			if (found === -1)
				this.visibleStatus.push(status);
			else
				this.visibleStatus.splice(found, 1);

			if (this.visibleStatus.length < 1)
				this.visibleStatus.push('all');
		},
		eventStyle(event) {
			if (!event.farbe)
				return undefined;
			return '--event-bg:#' + event.farbe;
		},
		updateRange(rangeInterval) {
			this.rangeInterval = rangeInterval;
			this.$emit('update:range', rangeInterval);
		},
		ondrop(payload){
			this.$emit('drop', payload);
		},
		resetEventLoader() {
			this.reset();
		},

	},
	setup(props, context) {
		const rangeInterval = Vue.ref(null);

		const { events, lv, reset  } = useEventLoader(rangeInterval, props.getPromiseFunc);

		Vue.watch(lv, newValue => {
			context.emit('update:lv', newValue);
		});

		return {
			rangeInterval,
			events,
			lv,
			reset
		};
	},

	created() {
		this.$api
			.call(ApiKalender.getStunden())
			.then(res => {
				return this.teachingunits = res.data.map(el => ({
					id: el.stunde,
					start: el.beginn,
					end: el.ende
				}));
			});
		this.$api.call(ApiTempusConfig.getHeader())
			.then(res => {
				this.visibleStatusArray = res.data.visible_status
				this.visibleStatus = ['all']
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
		:events="visibleEvents || []"
		:backgrounds="backgrounds"
		:time-grid="teachingunits"
		show-btns
		:draggable-events="true"
		:resizable-events="true"
		:on-drop="ondrop"
		@update:date="(newDate, newMode) => $emit('update:date', newDate, newMode)"
		@update:mode="(newMode, newDate) => $emit('update:mode', newMode, newDate)"
		@update:range="updateRange"
	>
		<template v-slot="{ event, mode }">
			<div
				:class="['event-type-' + event.type + ' ' + mode + 'PageContainer', { 'event--parked': parkedEvents.has(String(event.kalender_id)) }]"
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
			<slot>
				<button
					v-for="(status, key) in visibleStatusArray"
					:key="key"
					class="btn btn-sm me-1"
					:class="visibleStatus.includes(key) ? 'btn-secondary' : 'btn-outline-secondary'"
					@click="toggleStatus(key)"
				>
					{{ status }}
				</button>
			</slot>
		</template>
	</fhc-calendar>`
}
