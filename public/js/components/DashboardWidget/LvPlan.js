import AbstractWidget from './Abstract.js';
import FhcCalendar from '../Calendar/Base.js';

import ApiLvPlan from '../../api/factory/lvPlan.js';

import { useEventLoader } from '../../composables/EventLoader.js';

import ModeList from '../Calendar/Mode/List.js';

export default {
	name: "LvPlanWidget",
	components: {
		FhcCalendar
	},
	mixins: [
		AbstractWidget
	],
	inject: [
		"renderers",
		"timezone"
	],
	data() {
		return {
			now: luxon.DateTime.now().setZone(this.timezone),
			modes: {
				list: Vue.markRaw(ModeList)
			},
			modeOptions: {
				list: {
					length: 7
				}
			},
			currentDay: luxon.DateTime.now().setZone(this.timezone).startOf('day')
		}
	},
	methods: {
		eventStyle(event) {
			const styles = {};
			if (event.farbe)
				styles['--event-bg'] = '#' + event.farbe;
			else if (event.type == 'reservierung')
				styles['--event-bg'] = '#ffffff';
			else
				styles['--event-bg'] = '#cccccc';

			const eventEnd = luxon.DateTime.fromISO(event.isoend, { zone: this.timezone });
			if (eventEnd < this.now)
				styles['opacity'] = .5;
			
			return styles;
		},
		updateRange(rangeInterval) {
			this.rangeInterval = rangeInterval;
		}
	},
	setup() {
		const $api = Vue.inject('$api');

		const rangeInterval = Vue.ref(null);
		
		const { events } = useEventLoader(rangeInterval, (start, end) => {
			return [
				$api.call(ApiLvPlan.LvPlanEvents(start.toISODate(), end.toISODate())),
				$api.call(ApiLvPlan.getLvPlanReservierungen(start.toISODate(), end.toISODate()))
			];
		});

		return {
			rangeInterval,
			events
		};
	},
	created() {
		this.$emit('setConfig', false);
	},
	template: /*html*/`
	<div class="dashboard-widget-lvplan d-flex flex-column h-100">
		<fhc-calendar
			v-model:date="currentDay"
			:modes="modes"
			:mode-options="modeOptions"
			@update:range="updateRange"
			:timezone="timezone"
			:locale="$p.user_locale.value"
			:events="events"
		>
			<template v-slot="{ event, mode }">
				<div
					v-if="!event"
					class="h-100 d-flex justify-content-center align-items-center"
				>
					{{ $p.t('lehre/noLvFound') }}
				</div>
				<component
					v-else-if="mode == 'eventheader'"
					:is="renderers[event.type]?.modalTitle"
					:event="event"
				></component>
				<component
					v-else-if="mode == 'event'"
					:is="renderers[event.type]?.modalContent"
					:event="event"
				></component>
				<div
					v-else
					:class="'event-type-' + event.type + ' ' + mode + 'PageContainer'"
	 				:style="eventStyle(event)"
				>
					<component
						:is="renderers[event.type]?.calendarEvent"
						:event="event"
					></component>
				</div>
			</template>
		</fhc-calendar>
	</div>
`
}