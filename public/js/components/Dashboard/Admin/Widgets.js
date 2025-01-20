export default {
	emits: [
		"change",
		"assignWidgets"
	],
	props: {
		dashboard_id: Number,
		widgets: Array
	},
	computed: {
		apiurl() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/dashboard';
		}
	},
	methods: {
		sendChange(widget_id) {
			let allow = !this.widgets.find(el => el.widget_id == widget_id).allowed;
			axios.post(this.apiurl + '/Widget/setAllowed', {
				dashboard_id: this.dashboard_id,
				widget_id,
				action: allow ? 'add' : 'delete'
			}).catch(err => console.error('ERROR: ' + err));
		}
	},
	created() {
		axios.get(this.apiurl + '/Widget/getAll', {
			params:{
				dashboard_id: this.dashboard_id
			}
		}).then(
			result => {
				this.$emit('assignWidgets', result.data.retval.map(el => ({
					...el,
					...{setup:JSON.parse(el.setup),arguments:JSON.parse(el.arguments),allowed:!!el.allowed}
				})));
			}
		).catch(err => console.error('ERROR:', err));
	},
	template: `
	<div class="dashboard-admin-widgets">
		<div v-for="widget in widgets" :key="widget.widget_id" class="form-check form-switch">
			<input class="form-check-input" type="checkbox" role="switch" :id="'dashboard-admin-widgets-' + widget.widget_id" v-model="widget.allowed" @input.prevent="sendChange(widget.widget_id)">
			<label class="form-check-label" :for="'dashboard-admin-widgets-' + widget.widget_id">{{(widget.setup && widget.setup.name) || widget.widget_kurzbz}}</label>
		</div>
	</div>`
}
