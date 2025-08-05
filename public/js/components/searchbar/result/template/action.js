export default {
	name: 'SearchbarResultTemplateAction',
	emits: [ 'actionexecuted' ],
	props: { 
		res: Object,
		action: Object
	},
	computed: {
		actionHref() {
			if (this.action.type !== 'link')
				return 'javascript:void(0);';
			return typeof this.action.action === 'function'
				? this.action.action(this.res) 
				: this.action.action;
		}
	},
	methods: {
		actionFunc() {
			if (this.action.type === 'function')
				this.action.action(this.res);
			this.$emit('actionexecuted');
		}
	},
	template: `
	<a class="searchbar-result-template-action" :href="actionHref" @click="actionFunc">
		<slot>{{ $p.t('search/action_default_label') }}</slot>
	</a>`
};