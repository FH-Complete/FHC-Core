export default {
	props:{
		event: {
			type: Object,
			required: true,
		}
	},
	template:`
			<div >{{ event.titel }}</div>
`
}