export default {
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
			if (this.action.type !== 'function')
				return;
			this.action.action(this.res);
			this.$emit('actionexecuted');
		}
	},
	template: `
	<a :href="actionHref" @click="actionFunc">
		<slot>Action</slot>
	</a>`
};