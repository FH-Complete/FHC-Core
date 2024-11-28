import DashboardSection from "./Section.js";
import DashboardWidgetPicker from "./Widget/Picker.js";
import ObjectUtils from "../../helpers/ObjectUtils.js";

export default {
	components: {
		DashboardSection,
		DashboardWidgetPicker
	},
	props: [
		"dashboard",
		"viewDataString"
	],
	data() {
		return {
			sections: [],
			widgets: null,
			viewData: JSON.parse(this.viewDataString),
			editMode: false
		}
	},
	provide() {
		return {
			editMode: Vue.computed(()=>this.editMode),
		}
	},
	computed: {
		apiurl() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/dashboard';
		}
	},
	methods: {
		widgetAdd(section_name, widget) {
			if (this.widgets === null) {
				axios.get(this.apiurl + '/Widget/getWidgetsForDashboard', {params:{
					db: this.dashboard
				}}).then(res => {
					res.data.retval.forEach(widget => {
						widget.arguments = JSON.parse(widget.arguments);
						widget.setup = JSON.parse(widget.setup);
					});
					this.widgets = res.data.retval;
				}).catch(err => console.error('ERROR:', err));
			}
			this.$refs.widgetpicker.getWidget().then(widget_id => {
				widget.widget = widget_id;
				let loading = {...widget};
				loading.loading = true;
				this.sections.forEach(section => {
					if (section.name == section_name)
						section.widgets.push(loading);
				});
				
				axios.post(this.apiurl + '/Config/addWidgetsToUserOverride', {
					db: this.dashboard,
					funktion_kurzbz: section_name,
					widgets: [widget]
				}).then(result => {
					let newId = Object.keys(result.data.retval.data.widgets[section_name]).pop();
					widget.id = newId;
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
								for (var prop in {_x:1,_y:1,_w:1,_h:1,index:1,id:1,preset:1})
									if (payload[k][prop])
										delete payload[k][prop];
								break;
							}
						}
						break;
					}
				}
				payload[k].widgetid = k;
			}
			axios.post(this.apiurl + '/Config/addWidgetsToUserOverride', {
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
			axios.post(this.apiurl + '/Config/removeWidgetFromUserOverride', {
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
		}
	},
	created() {
		axios.get(this.apiurl + '/Config', {params:{
			db: this.dashboard
		}}).then(res => {
			for (var name in res.data.retval.widgets) {
				let widgets = [];
				let remove = [];
				for (var wid in res.data.retval.widgets[name]) {
					res.data.retval.widgets[name][wid].id = wid;
					if (res.data.retval.widgets[name][wid].custom || res.data.retval.widgets[name][wid].preset)
						widgets.push(res.data.retval.widgets[name][wid]);
					else
						remove.push(wid);
				}
				this.sections.push({
					name: name,
					widgets: widgets
				});
				remove.forEach(wid => this.widgetRemove(name, wid));
			}
		}).catch(err => console.error('ERROR:', err));
	},
	template: `
	<div class="core-dashboard">
		<h3>
			{{ $p.t('global/personalGreeting', [ viewData?.name ]) }}
			<button style="margin-left: 8px;" class="btn" @click="editMode = !editMode"><i class="fa-solid fa-gear"></i></button>
		</h3>
		<dashboard-section v-for="(section, index) in sections" :key="section.name" :seperator="index" :name="section.name" :widgets="section.widgets" @widgetAdd="widgetAdd" @widgetUpdate="widgetUpdate" @widgetRemove="widgetRemove"></dashboard-section>
		<dashboard-widget-picker ref="widgetpicker" :widgets="widgets"></dashboard-widget-picker>
	</div>`
}