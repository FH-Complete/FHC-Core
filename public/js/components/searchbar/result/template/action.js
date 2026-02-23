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
		},
		renderif: function() {
			if(this.action?.renderif === undefined) {
				return true;
			}

			return this.action.renderif(this.res);
		}
	},
	template: `
	<template v-if="this.renderif()">
	<a class="searchbar-result-template-action" :href="actionHref" @click="actionFunc">
		<slot>{{ $p.t('search/action_default_label') }}</slot>
	</a>
	</template>
	<template v-else>
	<div class="searchbar-result-template-action">
		<slot>{{ $p.t('search/action_default_label') }}</slot>
	</div>
	</template>`
};