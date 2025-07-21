import LabelDay from '../../Base/Label/Day.js';
import LabelDow from '../../Base/Label/Dow.js';

// TODO(chris): drag and drop

export default {
	name: "ListView",
	components: {
		LabelDay,
		LabelDow
	},
	inject: {
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
	template: /* html */`
	<div
		class="fhc-calendar-mode-list-view h-100"
	>
		<div v-if="!eventsPerDay.length">
			<slot :event="undefined" mode="list" />
		</div>
		<div v-for="{ day, events } in eventsPerDay" class="text-center">
			<label-dow :date="day" class="d-inline" />, <label-day :date="day" class="d-inline" />
			<div v-for="event in events">
 				<div v-if="slot.event.type == 'loading'" class="placeholder-glow opacity-50">
					<span class="placeholder w-100" />
				</div>
				<slot v-else :event="event.orig" mode="list" />
			</div>
		</div>
	</div>
	`
}
