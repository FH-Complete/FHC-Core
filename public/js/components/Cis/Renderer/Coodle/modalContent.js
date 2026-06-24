import { formatDate } from "../../../../helpers/DateHelpers.js"

export default {
	props:{
		event: {
			type: Object,
			required: true,
		},
		
	},
	data() {
		return {
			dateObject: null,
		};
	},
	methods:{
		formatDate
	},
	created() {
		this.dateObject = new Date(this.$props.event.isostart);
	},
	template: `
	<div>
		<span>{{ formatDate(dateObject) }}</span>
		<span>{{ $props.event.beginn.slice(0,5) }}</span>
		<span>{{ $props.event.end.slice(0,5) }}</span>
	</div>`,
}
