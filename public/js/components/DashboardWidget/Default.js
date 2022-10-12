import AbstractWidget from './Abstract';

export default {
	mixins: [
		AbstractWidget
	],
	computed: {
		css() {
			return ['dashboard-widget-default', this.config.css];
		}
	},
	created() {
		this.$emit('setConfig', false)
	},
	template: `<div :class="css">
	    <h5 class="card-title">{{ config.title }}</h5>
	    <p class="card-text">{{ config.msg }}</p>
	</div>`
}