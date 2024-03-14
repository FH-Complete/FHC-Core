export default {
	inject: [
		'$registerToForm'
	],
	data() {
		return {
			feedback: {
				success: [],
				danger: []
			}
		};
	},
	methods: {
		clearValidation() {
			this.feedback = {
				success: [],
				danger: []
			};
		},
		setFeedback(valid, feedback) {
			if (!feedback)
				feedback = [];
			if (!Array.isArray(feedback))
				feedback = [feedback];
			const ts = Date.now();
			this.feedback[valid ? 'success' : 'danger'] = feedback.map(msg => [msg, ts]);
		}
	},
	mounted() {
		if (this.$registerToForm)
			this.$registerToForm(this);
	},
	template: `
	<template v-for="(arr, key) in feedback" :key="key">
		<div v-for="[msg, ts] in arr" :key="ts + msg" class="alert alert-dismissible fade show" :class="'alert-' + key" role="alert">
			{{msg}}
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	</template>
	`
};
