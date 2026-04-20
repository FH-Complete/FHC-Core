export default {
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