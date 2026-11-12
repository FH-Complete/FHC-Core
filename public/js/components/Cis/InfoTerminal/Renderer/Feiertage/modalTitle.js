export default {
	name: 'Cis-Renderer-Feiertag-ModalTitle',
	props:{
		event: {
			type: Object,
			required: true,
		}
	},
	template:`
			<div>{{ $p.t('global','feiertag') }}</div>
`
}