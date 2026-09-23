export default {
	props:{
		event: {
			type: Object,
			required: true,
		}
	},
	template:`
			<div >{{ event.topic + ' [' + event.ort_kurzbz+']'}}</div>
`
}