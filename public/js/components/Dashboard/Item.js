export default {
	components: {},
	data: () => ({
		configMode: false,
		name: '',
		component: '',
		arguments: null
	}),
	props: [
		"widget",
		"config",
		"editMode"
	],
	computed: {
		ready() {
			return this.name && this.component && this.arguments !== null;
		}
	},
	methods: {
		changeConfig(v) {
			this.arguments = v;
			this.configMode = false;
			// TODO(chris): diff arguments widget.arguments
			this.$emit('change', v);
		}
	},
	mounted() {
		let self = this;

		if (!this.isPlaceholder) {
			this.$options.components[this.widget.component_name] = this.widget.component;
			this.name = this.widget.name;
			this.component = this.widget.component_name;
			this.arguments = {...this.widget.arguments, ...this.config};
		}
	},
	template: `<div class="core-dashboard-item col-sm-6 col-md-3">
		<div class="fixed-h fixed-h-1">
			<div v-if="ready" class="card">
				<div class="card-header d-flex">
					<span v-if="editMode" class="col-auto pe-3"><i class="fa-solid fa-grip-lines-vertical"></i></span>
					<span class="col">{{name}}</span>
					<a v-if="editMode" class="col-auto ps-1" href="#" @click.prevent="configMode = !configMode"><i class="fa-solid fa-gear"></i></a>
					<a v-if="editMode" class="col-auto ps-1" href="#" @click.prevent="$emit('remove')"><i class="fa-solid fa-trash-can"></i></a>
				</div>
				<div class="card-body">
					<component :is="component" :config="arguments" @config="changeConfig" :configMode="configMode"></component>
				</div>
			</div>
		</div>
	</div>`
}
