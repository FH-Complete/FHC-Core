const FEEDBACK_DEFAULT = {
	success: [],
	danger: []
};
export default {
	inject: [
		'$registerToForm'
	],
	data() {
		return {
			feedback: FEEDBACK_DEFAULT
		};
	},
	methods: {
		clearValidation() {
			this.feedback = FEEDBACK_DEFAULT;
		},
		setFeedback(valid, feedback) {
			if (!feedback)
				feedback = [];
			if (!Array.isArray(feedback))
				feedback = [feedback];
			this.feedback[valid ? 'success' : 'danger'] = feedback;
		}
	},
	mounted() {
		if (this.$registerToForm)
			this.$registerToForm(this);
	},
	template: `
	<template v-for="(arr, key) in feedback" :key="key">
		<div v-for="msg in arr" :key="msg" class="alert alert-dismissible fade show" :class="'alert-' + key" role="alert">
			{{msg}}
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	</template>
	`
};