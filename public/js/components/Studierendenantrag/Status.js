export default {
	props: {
		msg: String,
		severity: String
	},
	computed: {
		severityClass() {
			return 'alert-' + this.severity;
		}
	},
	template: `
	<div v-if="msg && severity" class="studierendenantrag-status alert text-center mb-3" :class="severityClass" role="alert" v-html="msg">
	</div>
	`
}
