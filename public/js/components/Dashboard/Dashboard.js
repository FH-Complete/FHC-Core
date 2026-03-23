import DashboardSection from "./Section.js";
import DashboardWidgetPicker from "./Widget/Picker.js";
import ObjectUtils from "../../helpers/ObjectUtils.js";

import ApiDashboardWidget from '../../api/factory/dashboard/widget.js';
import ApiDashboardUser from '../../api/factory/dashboard/user.js';

export default {
	name: 'Dashboard',
	components: {
		DashboardSection,
		DashboardWidgetPicker
	},
	props: {
		dashboard: {
			type: String,
			required: true,
			default: 'CIS'
		},
		viewData: {
			type: Object,
			required: true,
			validator(value) {
				return value && value.name && value.timezone
			}
		}
	},
	data() {
		return {
			widgets: [],
			originalWidgets: {},
			widgetsSetup: null,
			editMode: false
		}
	},
	provide() {
		return {
			editMode: Vue.computed(()=>this.editMode),
			widgetsSetup: Vue.computed(() => this.widgetsSetup),
			timezone: Vue.computed(() => this.viewData.timezone)
		}
	},
	methods: {
		widgetAdd(section_name, widget) {
			// TODO(chris): remove section_name? (change order of params => get rid of it)
			this.$refs.widgetpicker
				.getWidget()
				.then(widget_id => {
					widget.widget = widget_id;
					widget.id = 'loading_' + String((new Date()).valueOf());
					let loading = { ...widget };
					loading.loading = true;
					this.widgets.push(loading);
					
					this.$api
						.call(ApiDashboardUser.addWidget(this.dashboard, widget))
						.then(result => {
							widget.id = result.data;
							this.widgets.splice(this.widgets.indexOf(loading), 1);
							this.widgets.push(widget);
							this.originalWidgets[widget.id] = structuredClone(ObjectUtils.deepToRaw(widget));
						})
						.catch(this.$fhcAlert.handleSystemError);
				})
				.catch(() => {});
		},
		widgetUpdate(section_name, payload) {
			payload = payload[section_name];
			for (var k in payload) {
				for (var wid in this.widgets) {
					if (this.widgets[wid].id == k) {
						payload[k] = ObjectUtils.mergeDeep(this.widgets[wid], payload[k]);
						// NOTE(chris): remove internal props
						for (var prop of ['_x','_y','_w','_h','index','id','preset'])
							if (payload[k][prop])
								delete payload[k][prop];
						break;
					}
				}
				payload[k].widgetid = k;
			}
			this.$api
				.call(Object.entries(payload).map(([key, widget]) => [key, ApiDashboardUser.addWidget(this.dashboard, widget)]))
				.then(result => {
					const failed = result
						.filter(o => o.status == 'rejected')
						.map(o => o.reason.config.errorHeader);

					this.widgets.forEach((widget, i) => {
						if (failed.includes(widget.id)) {
							this.widgets[i] = structuredClone(ObjectUtils.deepToRaw(this.originalWidgets[widget.id]));
							/** NOTE(chris): if you wanna hide or unhide a
							 * preset and it fails: switch around the hidden
							 * value to revert it properly (checkboxes can't
							 * really handle it otherwise)
							 */
							if (payload[widget.id].hidden !== undefined) {
								this.widgets[i].hidden = payload[widget.id].hidden;
								this.$nextTick(() => {
									this.widgets[i] = structuredClone(ObjectUtils.deepToRaw(this.originalWidgets[widget.id]));
								});
							}
						} else if (payload[widget.id]) {
							payload[widget.id].id = widget.id;
							payload[widget.id].index = widget.index;
							this.widgets[i] = payload[widget.id];
							this.originalWidgets[widget.id] = structuredClone(ObjectUtils.deepToRaw(this.widgets[i]));
						}
					});
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		widgetRemove(section_name, id) {
			this.$api
				.call(ApiDashboardUser.removeWidget(this.dashboard, id))
				.then(() => {
					this.widgets = this.widgets.filter(widget => widget.id != id);
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	created() {
		this.$p.loadCategory('dashboard');

		this.$api
			.call(ApiDashboardWidget.listAllowed(this.dashboard))
			.then(res => {
				this.widgetsSetup = res.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiDashboardUser.get(this.dashboard))
			.then(res => {
				const widgets = [];
				const remove = [];

				for (var wid in res.data.general.widgets) {
					let widget = res.data.general.widgets[wid];
					widget.id = wid;
					if (widget.custom || widget.preset) {
						widgets.push(widget);
						this.originalWidgets[wid] = structuredClone(widget);
					} else {
						remove.push(wid);
					}
				}

				remove.forEach(wid => this.widgetRemove('general', wid));

				this.widgets = widgets;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="core-dashboard">
		<h3>
			{{ $p.t('global/personalGreeting', [ viewData?.name ]) }}
			<button style="margin-left: 8px;" class="btn" @click="editMode = !editMode" aria-label="edit dashboard" v-tooltip="{showDelay:1000,value:'edit dashboard'}"><i class="fa-solid fa-gear" aria-hidden="true"></i></button>
		</h3>
		<dashboard-section :seperator="0" name="general" :widgets="widgets" @widgetAdd="widgetAdd" @widgetUpdate="widgetUpdate" @widgetRemove="widgetRemove"></dashboard-section>
		<dashboard-widget-picker ref="widgetpicker" :widgets="widgetsSetup"></dashboard-widget-picker>
	</div>`
}
