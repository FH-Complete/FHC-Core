import FhcCalendar from "./Base.js";

import { useEventLoader } from '../../composables/EventLoader.js';
import { useRenderers } from '../../composables/Renderers.js';

import ModeList from '../Calendar/Mode/List.js';

import ApiRoomPlan from '../../api/factory/calendar/roomPlan.js';

export default {
	name: "CalendarWidget",
	components: {
		FhcCalendar
	},
	props: {
		getPromiseFunc: {
			type: Function,
			required: true
		}
	},
	data() {
		const timezone = FHC_JS_DATA_STORAGE_OBJECT.timezone;
		return {
			timezone,
			now: luxon.DateTime.now().setZone(timezone),
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
		},
		deleteEvent(event) {
			if (event.type === "reservierung") {
				this.deleteReservation(event);
			}
		},
		async deleteReservation(event) {
			if (
				luxon.DateTime.fromISO(`${event.datum}T${event.beginn}`) <
				luxon.DateTime.now()
			)
				return;

			await this.$api.call(
				ApiRoomPlan.deleteRoomReservation(event.reservierung_id),
			);

			this.reset();
		},
	},
	setup(props) {
		const rangeInterval = Vue.ref(null);
		
		const { events, reset } = useEventLoader(rangeInterval, props.getPromiseFunc);
		const { renderers } = useRenderers();

		return {
			rangeInterval,
			events,
			reset,
			renderers
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
			<div v-else-if="!renderers || !renderers[event.type]" class="placeholder-glow">
				<span class="placeholder col-12"></span>
			</div>
			<component
				v-else-if="renderers && mode == 'eventheader'"
				:is="renderers[event.type]?.modalTitle"
				:event="event"
			></component>
			<component
				v-else-if="renderers && mode == 'event'"
				:is="renderers[event.type]?.modalContent"
				:event="event"
			></component>
			<div
				v-else-if="renderers"
				:class="'event-type-' + event.type + ' ' + mode + 'PageContainer'"
 				:style="eventStyle(event)"
			>
				<component
					:is="renderers[event.type]?.calendarEvent"
					:event="event"
					:timeSlotDisplayBehavior="'always'"
					@delete-event="(event) => deleteEvent(event)"
				></component>
			</div>
		</template>
	</fhc-calendar>`
}
