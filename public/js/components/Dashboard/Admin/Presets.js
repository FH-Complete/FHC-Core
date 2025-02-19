import DashboardSection from "../Section.js";
import DashboardWidgetPicker from "../Widget/Picker.js";
import ObjectUtils from "../../../helpers/ObjectUtils.js";

export default {
	components: {
		DashboardSection,
		DashboardWidgetPicker
	},
	props: {
		dashboard: String,
		widgets: Array
	},
	data: () => ({
		funktionen: {},
		sections: [],
		tmpLoading: ''
	}),
	computed: {
		apiurl() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/dashboard';
		},
		pickerWidgets() {
			return this.widgets.filter(widget => widget.allowed);
		}
	},
	methods: {
		widgetAdd(section_name, widget) {
			this.$refs.widgetpicker.getWidget().then(widget_id => {
				widget.widget = widget_id;
				delete widget.custom;
				widget.preset = 1;
				let loading = {...widget};
				loading.loading = true;
				this.sections.forEach(section => {
					if (section.name == section_name)
						section.widgets.push(loading);
				});
				
				axios.post(this.apiurl + '/Config/addWidgetsToPreset', {
					db: this.dashboard,
					funktion_kurzbz: section_name,
					widgets: [widget]
				}).then(result => {
					let newId = Object.keys(result.data.retval.data.widgets[section_name]).pop();
					widget.id = newId;
					widget.custom = 1;
					this.sections.forEach(section => {
						if (section.name == section_name) {
							section.widgets.splice(section.widgets.indexOf(loading),1);
							section.widgets.push(widget);
						}
					});
				}).catch(error => {
					console.error('ERROR: ', error);
					alert('ERROR: ' + error.response.data.retval);
				});
			}).catch(() => {});
		},
		widgetUpdate(section_name, payload) {
			payload = payload[section_name];
			for (var k in payload) {
				for (var i in this.sections) {
					if (this.sections[i].name == section_name) {
						for (var wid in this.sections[i].widgets) {
							if (this.sections[i].widgets[wid].id == k) {
								payload[k] = ObjectUtils.mergeDeep(this.sections[i].widgets[wid], payload[k]);
								// NOTE(chris): remove internal props
								for (var prop in {_x:1,_y:1,_w:1,_h:1,index:1,id:1})
									if (payload[k][prop])
										delete payload[k][prop];
								break;
							}
						}
						break;
					}
				}
				payload[k].widgetid = k;
				delete payload[k].custom;
			}
			axios.post(this.apiurl + '/Config/addWidgetsToPreset', {
				db: this.dashboard,
				funktion_kurzbz: section_name,
				widgets: payload
			}).then(() => {
				this.sections.forEach(section => {
					if (section.name == section_name) {
						section.widgets.forEach((widget, i) => {
							if (payload[widget.id]) {
								payload[widget.id].id = widget.id;
								payload[widget.id].index = widget.index;
								section.widgets[i] = payload[widget.id];
								section.widgets[i].custom = 1;
							}
						});
					}
				});
			}).catch(error => {
				// TODO(chris): revert placement on failure
				console.error('ERROR: ', error);
				alert('ERROR: ' + error.response.data.retval);
			});
		},
		widgetRemove(section_name, id) {
			axios.post(this.apiurl + '/Config/removeWidgetFromPreset', {
				db: this.dashboard,
				funktion_kurzbz: section_name,
				widgetid: id
			}).then(() => {
				this.sections.forEach(section => {
					if (section.name == section_name)
						section.widgets = section.widgets.filter(widget => widget.id != id);
				});
			}).catch(error => {
				console.error('ERROR: ', error);
				alert('ERROR: ' + error.response.data.retval);
			});
		},
		loadSections(evt) {
			let funktionen = Array.from(evt.target.querySelectorAll("option:checked"),e=>e.value);
			this.sections = [];
			this.tmpLoading = funktionen.join('###');
			axios.get(this.apiurl + '/Config/presetBatch', {params: {
				db: this.dashboard,
				funktionen
			}}).then(res => {
				if (this.tmpLoading !== funktionen.join('###'))
					return; // NOTE(chris): prevent race condition
				
				for (var section in res.data.retval) {
					let widgets = [];
					for (var wid in res.data.retval[section]) {
						res.data.retval[section][wid].id = wid;
						res.data.retval[section][wid].custom = 1;
						widgets.push(res.data.retval[section][wid]);
					}
					this.sections.push({
						name: section,
						widgets
					});
				}
			}).catch(err => console.error('ERROR:', err));
		}
	},
	created() {
		axios.get(this.apiurl + '/Config/funktionen').then(res => {
			this.funktionen = {general: 'GENERAL'};
			res.data.retval.forEach(funktion => {
				this.funktionen[funktion.funktion_kurzbz] = funktion.beschreibung;
			});
		}).catch(err => console.error('ERROR:', err));
	},
	watch: {
		dashboard() {
			// TODO(chris): this should be done without a watcher
			this.loadSections({target:this.$refs.funktionenList});
		}
	},
	template: `<div class="dashboard-admin-presets">
		<div class="row">
			<div class="col-3">
				<select ref="funktionenList" style="height:30em" class="form-control" multiple @input="loadSections">
					<option v-for="name,id in funktionen" :key="id" :value="id">{{ name }}</option>
				</select>
			</div>
			<div class="col-9">
				<dashboard-section v-for="section in sections" :key="section.name" :name="section.name" :widgets="section.widgets" @widget-add="widgetAdd" @widget-update="widgetUpdate" @widget-remove="widgetRemove"></dashboard-section>
			</div>
		</div>
		<dashboard-widget-picker ref="widgetpicker" :widgets="pickerWidgets"></dashboard-widget-picker>
	</div>`
}
