export default {
	name: 'Cis-Renderer-Reservierung-ModalTitle',
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