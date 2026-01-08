import BsPrompt from "../Bootstrap/Prompt.js";
import DashboardAdminEdit from "./Admin/Edit.js";
import DashboardAdminWidgets from "./Admin/Widgets.js";
import DashboardAdminPresets from "./Admin/Presets.js";

import ApiDashboardAdmin from "../../api/factory/dashboard/dashboardAdmin.js";

export default {
	name: 'DashboardAdmin',
	components: {
		DashboardAdminEdit,
		DashboardAdminWidgets,
		DashboardAdminPresets,
	},
	provide() {
		return {
			adminMode: true
		};
	},
	data() {
		return {
			dashboards: [],
			current: -1,
			widgets: []
		};
	},
	computed: {
		dashboard() {
			return this.dashboards.find(el => el.dashboard_id == this.current);
		}
	},
	methods: {
		dashboardAdd() {
			let _name = '';
			BsPrompt.popup('New Dashboard name').then(
				name => {
					_name = name;
					const params = {
						dashboard_kurzbz: name
					};
					return this.$api
						.call(ApiDashboardAdmin.addDashboard(params))
						.then(response =>{
							this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));

							let newDashboard = {
								dashboard_id: response.data,
								dashboard_kurzbz: _name,
								beschreibung: ''
							};
							this.dashboards.push(newDashboard);
							this.current = newDashboard.dashboard_id;
						})
						.catch(this.$fhcAlert.handleSystemError);
					});
		},
		dashboardUpdate(dashboard) {
			return this.$api
				.call(ApiDashboardAdmin.updateDashboard(dashboard))
				.then(response =>{

					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));

					let old = this.dashboards.find(el => el.dashboard_id == dashboard.dashboard_id);
					old.dashboard_kurzbz = dashboard.dashboard_kurzbz;
					old.beschreibung = dashboard.beschreibung;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		dashboardDelete(dashboard_id) {
			return this.$api
				.call(ApiDashboardAdmin.deleteDashboard(dashboard_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));

				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.current = -1;
					this.dashboards = this.dashboards.filter(el => el.dashboard_id != dashboard_id);
				});
		},
		assignWidgets(widgets) {
			this.widgets = widgets;
			/*while (this.widgets.length)
				this.widgets.pop();
			for (var i in widgets)
				this.widgets.push(widgets[i]);*/
		}
	},
	created() {
		this.$api
			.call(ApiDashboardAdmin.getAllDashboards())
			.then(result => {
				this.dashboards = result.data.retval;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `<div class="dashboard-admin">

		<div class="input-group">
			<label for="dashboard-select" class="input-group-text">Dashboard:</label>
			<select id="dashboard-select" class="form-select" v-model="current">
				<option v-for="dashboard in dashboards" :key="dashboard.dashboard_id" :value="dashboard.dashboard_id">{{dashboard.dashboard_kurzbz}}</option>
			</select>
			<button class="btn btn-outline-secondary" type="button" @click="dashboardAdd"><i class="fa-solid fa-plus"></i></button>
		</div>

		<div v-if="dashboard">
			<ul class="nav nav-tabs mt-3" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit" type="button" role="tab" aria-controls="edit" aria-selected="false">{{this.$p.t('ui', 'bearbeiten')}}</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link active" id="widgets-tab" data-bs-toggle="tab" data-bs-target="#widgets" type="button" role="tab" aria-controls="widgets" aria-selected="true">Widgets</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="presets-tab" data-bs-toggle="tab" data-bs-target="#presets" type="button" role="tab" aria-controls="presets" aria-selected="false">Presets</button>
				</li>
			</ul>
			<div class="tab-content pt-3">
				<div class="tab-pane fade" id="edit" role="tabpanel" aria-labelledby="edit-tab">
					<dashboard-admin-edit v-bind="dashboard" :key="dashboard.dashboard_id" @change="dashboardUpdate($event)" @delete="dashboardDelete($event)"></dashboard-admin-edit>
				</div>
				<div class="tab-pane fade show active" id="widgets" role="tabpanel" aria-labelledby="widgets-tab">
					<dashboard-admin-widgets :key="dashboard.dashboard_id" :dashboard_id="dashboard.dashboard_id" :widgets="widgets" @assign-widgets="assignWidgets"></dashboard-admin-widgets>
				</div>
				<div class="tab-pane fade" id="presets" role="tabpanel" aria-labelledby="presets-tab">
					<dashboard-admin-presets :dashboard="dashboard.dashboard_kurzbz" :widgets="widgets"></dashboard-admin-presets>
				</div>
			</div>
		</div>
	</div>`
}
