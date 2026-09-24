export default {
	name: 'CmsMenuSwitch',
	props: {
		modelValue: { type: String, default: 'content' }
	},
	emits: ['update:modelValue'],
	methods: {
		select(value) {
			localStorage.setItem('cms/menu', value);
			this.$emit('update:modelValue', value);
		}
	},
	mounted() {
		// The legacy code keeps this choice in $_SESSION['cms/menu']. The Vue client uses
		// localStorage. The effect for the editor is the same.
		const stored = localStorage.getItem('cms/menu');
		if ((stored === 'content' || stored === 'news') && stored !== this.modelValue) {
			this.$emit('update:modelValue', stored);
		}
	},
	template: `
		<div class="btn-group btn-group-sm w-100" role="group">
			<button type="button" class="btn"
				:class="modelValue === 'content' ? 'btn-primary' : 'btn-outline-secondary'"
				@click="select('content')">
				{{ $p.t('cms/menuContent') }}
			</button>
			<button type="button" class="btn"
				:class="modelValue === 'news' ? 'btn-primary' : 'btn-outline-secondary'"
				@click="select('news')">
				{{ $p.t('cms/menuNews') }}
			</button>
		</div>
	`
};
