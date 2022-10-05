import AbstractWidget from './Abstract';

export default {
	mixins: [
		AbstractWidget
	],
	created() {
		this.$emit('setConfig', false)
	},
	template: `<div class="dashboard-widget-default">
	    <h5 class="card-title">{{ config.title }}</h5>
	    <p class="card-text">{{ config.msg }}</p>
	</div>`
}