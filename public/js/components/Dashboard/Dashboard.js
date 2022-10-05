import DashboardSection from "./Section.js";
import CachedWidgetLoader from "../../composables/Dashboard/CachedWidgetLoader.js";

export default {
	props: [
		"dashboard",
		"apiurl"
	],
	data: () => ({
		sections: [],
		widgets: [],
		isLoading: 0,
		tmpCreate: null,
	}),
	components: {
		DashboardSection
	},
	computed: {
		listReady() {
			return this.widgets.length && !this.isLoading;
		}
	},
	methods: {
		widgetAdd(section_name, widget) {
			if (!this.widgets.length) {
				axios.get(this.apiurl + '/Widget/getWidgetsForDashboard', {params:{
					db: this.dashboard
				}}).then(res => {
					//console.log(res.data.retval);
					this.widgets = res.data.retval;
				}).catch(err => console.error('ERROR:', err));
			}
			this.tmpCreate = {section_name,widget};
			this.listModal.show();
		},
		widgetCreate(widget) {
			this.isLoading = 1;
			this.tmpCreate.widget.widget = widget;
			axios.post(this.apiurl + '/Config/addWidgetsToUserOverride', {
				db: this.dashboard,
				uid: 'ma0168',
				funktion_kurzbz: this.tmpCreate.section_name,
				widgets: [this.tmpCreate.widget]
			}).then(result => {
				let newId = 0;
				let sec = result.data.retval.data.widgets[this.tmpCreate.section_name];
				for (var i in sec) {
					newId = i;
					break;
				}
				console.log(newId);
				this.tmpCreate.widget.id = newId;
				this.sections.forEach(section => {
					if (section.name == this.tmpCreate.section_name)
						section.widgets.push(this.tmpCreate.widget);
				});
			}).catch(error => {
				console.error('ERROR: ', error);
				alert('ERROR: ' + error.response.data.retval);
			}).finally(() => {
				this.listModal.hide();
				this.isLoading = 0;
			});
		},
		widgetUpdate(section_name, payload) {
			payload = payload[section_name];
			for (var k in payload) {
				for (var i in this.sections) {
					if (this.sections[i].name == section_name) {
						for (var wid in this.sections[i].widgets) {
							if (this.sections[i].widgets[wid].id == k) {
								payload[k] = {...this.sections[i].widgets[wid], ...payload[k]};
								break;
							}
						}
						break;
					}
				}
				payload[k].widgetid = k;
			}
			return axios.post(this.apiurl + '/Config/addWidgetsToUserOverride', {
				db: this.dashboard,
				uid: 'ma0168',
				funktion_kurzbz: section_name,
				widgets: payload
			}).then(result => {
				this.sections.forEach(section => {
					if (section.name == section_name) {
						section.widgets.forEach((widget, i) => {
							if (payload[widget.id]) {
								// TODO(chris): revert placement on failure
								delete payload[widget.id].place; // TODO(chris): find out why overwriting place bugs out
								section.widgets[i] = {...widget, ...payload[widget.id]};
							}
						});
					}
				});
			}).catch(error => {
				console.error('ERROR: ', error);
				alert('ERROR: ' + error.response.data.retval);
			});
		},
		widgetRemove(section_name, id) {
			axios.post(this.apiurl + '/Config/removeWidgetFromUserOverride', {
				db: this.dashboard,
				uid: 'ma0168',
				funktion_kurzbz: section_name,
				widgetid: id
			}).then(result => {
				this.sections.forEach(section => {
					if (section.name == section_name)
						section.widgets = section.widgets.filter(widget => widget.id != id);
				});
			}).catch(error => {
				console.error('ERROR: ', error);
				alert('ERROR: ' + error.response.data.retval);
			});
		}
	},
	created() {
		CachedWidgetLoader.setPath(this.apiurl + '/Widget');
		axios.get(this.apiurl + '/Config', {params:{
			db: this.dashboard,
			uid: 'ma0168'
		}}).then(res => {
			//console.log(res.data.retval);
			for (var name in res.data.retval.widgets) {
				let widgets = [];
				for (var wid in res.data.retval.widgets[name]) {
					res.data.retval.widgets[name][wid].id = wid;
					widgets.push(res.data.retval.widgets[name][wid]);
				}
				this.sections.push({
					name: name,
					widgets: widgets
				});
			}
		}).catch(err => console.error('ERROR:', err));
	},
	mounted() {
		this.listModal = new bootstrap.Modal(this.$refs.widgetlist);
	},
	template: `<div class="core-dashboard">
		<dashboard-section v-for="section in sections" :key="section.name" :name="section.name" :widgets="section.widgets" @widgetAdd="widgetAdd" @widgetUpdate="widgetUpdate" @widgetRemove="widgetRemove"></dashboard-section>
		<div ref="widgetlist" class="modal" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Create new widget</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div v-if="listReady" class="row">
							<div v-for="widget in widgets" :v-key="widget.id" class="col">
								<div class="card h-100" @click="widgetCreate(widget.id)">
									<img class="card-img-top" :src="widget.icon" :alt="'pictogram for ' + widget.name">
									<div class="card-body">
										<h5 class="card-title">{{ widget.name }}</h5>
										<p class="card-text">{{ widget.description }}</p>
									</div>
								</div>
							</div>
						</div>
						<div v-else class="text-center"><i class="fa-solid fa-spinner fa-pulse fa-3x"></i></div>
					</div>
				</div>
			</div>
		</div>
	</div>`
}