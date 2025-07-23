import LabelDay from '../../Base/Label/Day.js';
import LabelDow from '../../Base/Label/Dow.js';

import CalDnd from '../../../../directives/Calendar/DragAndDrop.js';
import CalClick from '../../../../directives/Calendar/Click.js';

// TODO(chris): drag and drop

export default {
	name: "ListView",
	components: {
		LabelDay,
		LabelDow
	},
	directives: {
		CalDnd,
		CalClick
	},
	inject: {
		draggableEvents: "draggableEvents",
		events: "events"
	},
	props: {
		day: {
			type: luxon.DateTime,
			required: true
		},
		length: {
			type: Number,
			required: true
		}
	},
	data() {
		return {
			chosenEvent: null
		};
	},
	computed: {
		days() {
			return Array.from({ length: this.length }, (e, days) => this.day.plus({ days }));
		},
		eventsPerDay() {
			const eventsPerDay = this.days.map(day => {
				return {
					day,
					events: this.events
						.filter(event => event.start < day.plus({ days: 1 }) && event.end > day)
						.sort((a, b) => a.start.ts - b.start.ts)
				};
			});
			return eventsPerDay.filter(day => day.events.length);
		}
	},
	methods: {
		draggable(event) {
			return this.draggableEvents(event.orig, 'list');
		},
	},
	template: /* html */`
	<div class="fhc-calendar-mode-list-view h-100 overflow-auto">
		<div v-if="!eventsPerDay.length" class="h-100">
			<slot :event="undefined" mode="list" />
		</div>
		<div v-for="{ day, events } in eventsPerDay" class="text-center">
			<label-dow
				:date="day"
				class="d-inline"
				@cal-click="evt => evt.detail.source = 'day'"
			/>
			, 
			<label-day :date="day" class="d-inline" />
			<div v-for="event in events">
 				<div v-if="event.type == 'loading'" class="placeholder-glow opacity-50">
					<span class="placeholder w-100" />
				</div>
				<div
					v-else
					class="event"
					:draggable="draggable(event)"
					v-cal-dnd:draggable="event"
					v-cal-click:event="event.orig"
				>
					<slot :event="event.orig" mode="list" />
				</div>
			</div>
		</div>
	</div>
	`
}
