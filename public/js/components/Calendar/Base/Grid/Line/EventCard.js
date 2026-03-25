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
	computed: {
		dragKalenderCollection() {
			return this.event
		},
	},
	template: `
	<div
		class="fhc-calendar-base-grid-line-event event"
		v-draggable:move.noimage="dragKalenderCollection"
		style="border:1px"
	>
		<div class="title">
			{{ event.orig.topic || event.orig.titel || event.orig.lehrfach }}
		</div>
		<div>
			{{ event.orig.datum }} {{ event.orig.beginn }}–{{ event.orig.ende }}
			<span v-if="event.ort_kurzbz">· {{ event.orig.ort_kurzbz }}</span>
		</div>
	</div>
`
};
