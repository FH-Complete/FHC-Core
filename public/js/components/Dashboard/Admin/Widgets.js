import ApiDashboardWidget from "../../../api/factory/dashboard/widget.js";

export default {
	emits: [
		"change",
		"assignWidgets"
	],
	props: {
		dashboard_id: Number,
		widgets: Array
	},
	methods: {
		sendChange(widget_id) {
			let allow = !this.widgets.find(el => el.widget_id == widget_id).allowed;

			this.$api
				.call(ApiDashboardWidget.setAllowed(this.dashboard_id, widget_id, allow))
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	created() {
		this.$api
			.call(ApiDashboardWidget.list(this.dashboard_id))
			.then(result => {
				this.$emit('assignWidgets', result.data.map(el => ({
					...el,
					...{setup:JSON.parse(el.setup),arguments:JSON.parse(el.arguments),allowed:!!el.allowed}
				})));
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="dashboard-admin-widgets">
		<div v-for="widget in widgets" :key="widget.widget_id" class="form-check form-switch">
			<input class="form-check-input" type="checkbox" role="switch" :id="'dashboard-admin-widgets-' + widget.widget_id" v-model="widget.allowed" @input.prevent="sendChange(widget.widget_id)">
			<label class="form-check-label" :for="'dashboard-admin-widgets-' + widget.widget_id">{{(widget.setup && widget.setup.name) || widget.widget_kurzbz}}</label>
		</div>
	</div>`
}
