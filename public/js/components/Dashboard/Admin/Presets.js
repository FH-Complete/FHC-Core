import DashboardSection from "../Section.js";
import DashboardWidgetPicker from "../Widget/Picker.js";
import ObjectUtils from "../../../helpers/ObjectUtils.js";
import ApiDashboardPreset from "../../../api/factory/dashboard/preset.js";

export default {
	components: {
		DashboardSection,
		DashboardWidgetPicker
	},
	props: {
		dashboard: String,
		widgets: Array
	},
	data() {
		return {
			funktionen: {},
			sections: [],
			selectedFunktionen: [],
			abortController: null
		};
	},
	computed: {
		pickerWidgets() {
			return this.widgets.filter(widget => widget.allowed);
		}
	},
	watch: {
		dashboard() {
			this.loadSections();
			this.loadFunktionen();
		}
	},
	methods: {
		widgetAdd(widget, section_name) {
			this.$refs.widgetpicker.getWidget().then(widget_id => {
				widget.widget = widget_id;
				widget.id = 'loading_' + String((new Date()).valueOf());
				delete widget.custom;
				widget.preset = 1;
				let loading = {...widget};
				loading.loading = true;
				this.sections.forEach(section => {
					if (section.name == section_name)
						section.widgets.push(loading);
				});

				const params = {
					dashboard: this.dashboard,
					funktion_kurzbz: section_name,
					widget
				};

				return this.$api
					.call(ApiDashboardPreset.addWidget(params))
					.then(result => {
						let newId = result.data;
						widget.id = newId;
						widget.custom = 1;
						this.sections.forEach(section => {
							if (section.name == section_name) {
								section.widgets.splice(section.widgets.indexOf(loading),1);
								section.widgets.push(widget);
							}
						});
						this.funktionen.forEach(funktion => {
							if(funktion.funktion_kurzbz === section_name && funktion.has_preset < 1) {
								funktion.has_preset = 1;
							}
						});
					})
					.catch(this.$fhcAlert.handleSystemError);
			})
			.catch(() => {});
		},
		widgetUpdate(payload, section_name) {
			for (var k in payload) {
				const section = this.sections.find(section => section.name == section_name);
				for (var wid in section.widgets) {
					if (section.widgets[wid].id == k) {
						payload[k] = ObjectUtils.mergeDeep(section.widgets[wid], payload[k]);
						// NOTE(chris): remove internal props
						for (var prop of ['_x', '_y', '_w', '_h', 'index', 'id', 'custom'])
							if (payload[k][prop])
								delete payload[k][prop];
						break;
					}
				}
				if (payload[k].place) {
					Object.values(payload[k].place).forEach(place => {
						if (place.pinned === false)
							delete place.pinned;
					});
				}
				payload[k].widgetid = k;
			}
			this.$api
				.call(Object.entries(payload).map(([key, widget]) => [
					key,
					ApiDashboardPreset.addWidget({
						dashboard: this.dashboard,
						funktion_kurzbz: section_name,
						widget
					})
				]))
				.then(result => {
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
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		widgetRemove(id, section_name) {
			const params = {
				db: this.dashboard,
				funktion_kurzbz: section_name,
				widgetid: id
			};
			return this.$api
				.call(ApiDashboardPreset.removeWidget(params))
				.then(result => {
					this.sections.forEach(section => {
						if (section.name == section_name)
							section.widgets = section.widgets.filter(widget => widget.id != id);
					});
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		loadSections() {
			const params = {
				db: this.dashboard,
				funktionen: this.selectedFunktionen
			};

			if (this.abortController)
				this.abortController.abort();
			this.abortController = new AbortController();
			const signal = this.abortController.signal;

			this.sections = [];
			
			return this.$api
				.call(ApiDashboardPreset.getBatch(params), { signal })
				.then(result => {
					for (var section in result.data) {
						let widgets = [];
						for (var wid in result.data[section]) {
							result.data[section][wid].id = wid;
							result.data[section][wid].custom = 1;
							widgets.push(result.data[section][wid]);
						}
						this.sections.push({
							name: section,
							widgets
						});
					}
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		loadFunktionen() {
			this.$api
				.call(ApiDashboardPreset.list(this.dashboard))
				.then(result => {
					this.funktionen = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	created() {
		this.loadFunktionen();
	},
	template: /* html */`
	<div class="dashboard-admin-presets">
		<div class="row">
			<div class="col-3">
				<select
					v-model="selectedFunktionen"
					class="form-control"
					style="height:30em"
					multiple
					@change="loadSections"
				>
					<option
						v-for="funktion in funktionen"
						:key="funktion.funktion_kurzbz"
						:value="funktion.funktion_kurzbz"
						:class="(funktion.has_preset > 0) ? 'fw-bold' : ''"
					>{{ funktion.beschreibung }}</option>
				</select>
			</div>
			<div class="col-9">
				<dashboard-section
					v-for="section in sections"
					:key="section.name"
					:name="section.name"
					:widgets="section.widgets"
					@widget-add="widgetAdd"
					@widget-update="widgetUpdate"
					@widget-remove="widgetRemove"
				></dashboard-section>
			</div>
		</div>
		<dashboard-widget-picker
			ref="widgetpicker"
			:widgets="pickerWidgets"
		></dashboard-widget-picker>
	</div>`
}
