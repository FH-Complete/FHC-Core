import LabelDay from '../../Base/Label/Day.js';
import LabelDow from '../../Base/Label/Dow.js';

import CalendarDate from '../../../../helpers/Calendar/Date.js';

// TODO(chris): drag and drop

export default {
	name: "ListView",
	components: {
		LabelDay,
		LabelDow
	},
	inject: {
		locale: "locale",
		events: "events"
	},
	props: {
		day: Number,
		length: Number
	},
	data() {
		return {
			chosenEvent: null
		};
	},
	computed: {
		days() {
			return Array.from({ length: this.length }, (e, i) => this.day + i * CalendarDate.msPerDay);
		},
		eventsPerDay() {
			const eventsPerDay = this.days.map(day => {
				return {
					day,
					events: this.events
						.filter(event => event.start < day + CalendarDate.msPerDay && event.end > day)
						.sort((a, b) => a.start - b.start)
				};
			});
			return eventsPerDay.filter(day => day.events.length);
			return eventsPerDay;
		}
	},
	template: `
	<div
		class="fhc-calendar-mode-list-view h-100"
	>
		<div v-if="!eventsPerDay.length">
			<slot :event="undefined" mode="list" />
		</div>
		<div v-for="{ day, events } in eventsPerDay">
			<label-dow :timestamp="day" />, <label-day :timestamp="day" />
			<div v-for="event in events">
				<slot :event="event.orig" mode="list" />
			</div>
		</div>
	</div>
	`
}
