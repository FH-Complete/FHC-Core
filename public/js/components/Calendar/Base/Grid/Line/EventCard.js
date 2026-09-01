import draggable from '../../../../../directives/draggable.js';

export default {
	name: 'EventCard',
	directives: {
		draggable,
	},
	props: {
		event: { type: Object, required: true },
		parked: Boolean
	},
	emits: [
		'select-kw',
		'unpark'
	],
	computed: {
		dragKalenderCollection() {
			return this.event
		},
		topicString() {
			return Array.isArray(this.event.orig.topic) ? this.event.orig.topic.join(', ') : this.event.orig.topic;
		},
		datum() {
			return luxon.DateTime.fromISO(this.event.orig.datum).toFormat('dd.MM.yyyy')
		},
		kw() {
			return luxon.DateTime.fromISO(this.event.orig.datum).startOf('week', { useLocaleWeeks: true }).localWeekNumber
		},
	},
	methods: {
		eventStyle(event) {
			if (!event.farbe)
				return undefined;
			return '--event-bg:#' + event.farbe;
		},
		jumpToKw()
		{
			this.$emit('select-kw', this.kw);
		},
		unpark(evt)
		{
			this.$emit('unpark', this.event);
		}
	},

	template: `
	<div
		class="course-parker event"
		v-draggable:move.noimage="dragKalenderCollection"
		:style="eventStyle(event.orig)"
		data-cy="calendar-event"
	>
		<button v-if="parked" class="unpark-btn" type="button" @click="unpark">
			<i class="fa-solid fa-xmark"></i>
		</button>
		<div class="title">
			{{ topicString || event.orig.titel || event.orig.lehrfach }}
		</div>
		<div>
			<span class="text-decoration-underline" @click="jumpToKw">KW: {{ kw }}</span> {{ datum }}
		</div>
		<div>
			{{ event.orig.beginn }}–{{ event.orig.ende }}
		</div>
	</div>
`
};