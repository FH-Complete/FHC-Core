import FhcCalendar from "./Base.js";

import { useEventLoader } from '../../composables/EventLoader.js';

import ModeList from '../Calendar/Mode/List.js';

export default {
	name: "CalendarWidget",
	components: {
		FhcCalendar
	},
	inject: [
		"renderers"
	],
	props: {
		timezone: {
			type: String,
			required: true
		},
		getPromiseFunc: {
			type: Function,
			required: true
		}
	},
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
			}
		};
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
	setup(props) {
		const rangeInterval = Vue.ref(null);
		
		const { events } = useEventLoader(rangeInterval, props.getPromiseFunc);

		return {
			rangeInterval,
			events
		};
	},
	template: /* html */`
	<fhc-calendar
		:modes="modes"
		:mode-options="modeOptions"
		:timezone="timezone"
		:locale="$p.user_locale.value"
		:events="events || []"
		@update:range="updateRange"
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
	</fhc-calendar>`
}
