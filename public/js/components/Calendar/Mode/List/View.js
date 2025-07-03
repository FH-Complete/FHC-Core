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
		day: luxon.DateTime,
		length: Number
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
	template: `
	<div
		class="fhc-calendar-mode-list-view h-100"
	>
		<div v-if="!eventsPerDay.length">
			<slot :event="undefined" mode="list" />
		</div>
		<div v-for="{ day, events } in eventsPerDay">
			<label-dow :timestamp="day.ts" class="d-inline" />, <label-day :timestamp="day.ts" class="d-inline" />
			<div v-for="event in events">
				<slot :event="event.orig" mode="list" />
			</div>
		</div>
	</div>
	`
}
