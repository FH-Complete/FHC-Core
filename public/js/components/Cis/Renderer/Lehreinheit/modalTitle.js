export default {
	props:{
		event: {
			type: Object,
			required: true,
		}
	},
	template:`
			<div v-if="event.titel">{{ event.titel + ' - ' + event.lehrfach_bez + ' [' + event.ort_kurzbz+']'}}</div>
			<div v-else>{{ event.lehrfach_bez + ' [' + event.ort_kurzbz+']'}}</div>
`
}