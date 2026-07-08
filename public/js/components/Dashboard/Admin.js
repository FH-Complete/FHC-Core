import BsPrompt from "../Bootstrap/Prompt.js";
import DashboardAdminEdit from "./Admin/Edit.js";
import DashboardAdminWidgets from "./Admin/Widgets.js";
import DashboardAdminPresets from "./Admin/Presets.js";

import ApiDashboardBoard from "../../api/factory/dashboard/board.js";

export default {
	name: 'DashboardAdmin',
	components: {
		DashboardAdminEdit,
		DashboardAdminWidgets,
		DashboardAdminPresets,
	},
	provide() {
		return {
			adminMode: true,
			widgetsSetup: Vue.computed(() => this.dashboard ? this.dashboard.widgetSetup : null)
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
			BsPrompt
				.popup('New Dashboard name')
				.then(dashboard_kurzbz => {
					const params = {
						dashboard_kurzbz
					};
					return this.$api
						.call(ApiDashboardBoard.add(params))
						.then(response => {
							this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));

							let newDashboard = {
								dashboard_id: response.data,
								dashboard_kurzbz,
								beschreibung: ''
							};
							this.dashboards.push(newDashboard);
							this.current = newDashboard.dashboard_id;
						})
						.catch(this.$fhcAlert.handleSystemError);
				});
		},
		dashboardUpdate(dashboard) {
			this.$api
				.call(ApiDashboardBoard.update(dashboard))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));

					let old = this.dashboards.find(el => el.dashboard_id == dashboard.dashboard_id);
					old.dashboard_kurzbz = dashboard.dashboard_kurzbz;
					old.beschreibung = dashboard.beschreibung;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		dashboardDelete(dashboard_id) {
			this.$api
				.call(ApiDashboardBoard.delete(dashboard_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));

					this.current = -1;
					this.dashboards = this.dashboards.filter(el => el.dashboard_id != dashboard_id);
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		assignWidgets(widgets) {
			this.widgets = widgets;
		}
	},
	created() {
		this.$api
			.call(ApiDashboardBoard.list())
			.then(result => {
				this.dashboards = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: /* html */`
	<div class="dashboard-admin">
		<div class="input-group">
			<label for="dashboard-select" class="input-group-text">
				Dashboard:
			</label>
			<select id="dashboard-select" v-model="current" class="form-select">
				<option
					v-for="dashboard in dashboards"
					:key="dashboard.dashboard_id"
					:value="dashboard.dashboard_id"
				>{{ dashboard.dashboard_kurzbz }}</option>
			</select>
			<button
				class="btn btn-outline-secondary"
				type="button"
				@click="dashboardAdd"
			><i class="fa-solid fa-plus"></i></button>
		</div>

		<div v-if="dashboard">
			<ul class="nav nav-tabs mt-3" role="tablist">
				<li class="nav-item" role="presentation">
					<button
						id="edit-tab"
						class="nav-link"
						data-bs-toggle="tab"
						data-bs-target="#edit"
						type="button"
						role="tab"
						aria-controls="edit"
						aria-selected="false"
					>{{ this.$p.t('ui', 'bearbeiten') }}</button>
				</li>
				<li class="nav-item" role="presentation">
					<button
						id="widgets-tab"
						class="nav-link active"
						data-bs-toggle="tab"
						data-bs-target="#widgets"
						type="button"
						role="tab"
						aria-controls="widgets"
						aria-selected="true"
					>Widgets</button>
				</li>
				<li class="nav-item" role="presentation">
					<button
						class="nav-link"
						id="presets-tab"
						data-bs-toggle="tab"
						data-bs-target="#presets"
						type="button"
						role="tab"
						aria-controls="presets"
						aria-selected="false"
					>Presets</button>
				</li>
			</ul>
			<div class="tab-content pt-3">
				<div
					id="edit"
					class="tab-pane fade"
					role="tabpanel"
					aria-labelledby="edit-tab"
				>
					<dashboard-admin-edit
						v-bind="dashboard"
						@change="dashboardUpdate($event)"
						@delete="dashboardDelete($event)"
					></dashboard-admin-edit>
				</div>
				<div
					id="widgets"
					class="tab-pane fade show active"
					role="tabpanel"
					aria-labelledby="widgets-tab"
				>
					<dashboard-admin-widgets
						:dashboard_id="dashboard.dashboard_id"
						:widgets="widgets"
						@assign-widgets="assignWidgets"
					></dashboard-admin-widgets>
				</div>
				<div
					id="presets"
					class="tab-pane fade"
					role="tabpanel"
					aria-labelledby="presets-tab"
				>
					<dashboard-admin-presets
						:dashboard="dashboard.dashboard_kurzbz"
						:widgets="widgets"
					></dashboard-admin-presets>
				</div>
			</div>
		</div>
	</div>`
}
