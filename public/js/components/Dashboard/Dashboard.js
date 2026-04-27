import DashboardSection from "./Section.js";
import DashboardWidgetPicker from "./Widget/Picker.js";
import ObjectUtils from "../../helpers/ObjectUtils.js";

import ApiDashboard from '../../api/factory/cis/dashboard.js';
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
	},
	data() {
		return {
			widgets: [],
			originalWidgets: {},
			widgetsSetup: null,
			editMode: false,
			timezone: null,
			userFirstName: null,
		}
	},
	provide() {
		return {
			editMode: Vue.computed(() => this.editMode),
			widgetsSetup: Vue.computed(() => this.widgetsSetup),
			timezone: this.timezone
		}
	},
	computed: {
		sizeLimits() {
			return Object.fromEntries(this.widgetsSetup.map(({ setup, widget_id: type }) => {
				const result = {}; // work on a copy
				if (setup.height === undefined)
					result.height = { min: 1, max: undefined };
				else if (Number.isInteger(setup.height))
					result.height = { min: setup.height, max: setup.height };
				else
					result.height = {
						min: setup.height.min ?? 1,
						max: setup.height.max
					};

				if (setup.width === undefined)
					result.width = { min: 1, max: undefined };
				else if (Number.isInteger(setup.width))
					result.width = { min: setup.width, max: setup.width };
				else
					result.width = {
						min: setup.width.min ?? 1,
						max: setup.width.max
					};

				return [type, result];
			}));
		}
	},
	methods: {
		widgetAdd(widget) {
			this.$refs.widgetpicker
				.getWidget()
				.then(widget_id => {
					widget.widget = widget_id;
					// NOTE(chris): min size
					widget.place = Object.fromEntries(Object.entries(widget.place).map(([key, value]) => {
						value.w = this.sizeLimits[widget_id].width.min;
						value.h = this.sizeLimits[widget_id].height.min;
						return [key, value];
					}));
					widget.id = 'loading_' + String((new Date()).valueOf());
					let loading = { ...widget };
					loading.loading = true;
					this.widgets.push(loading);

					delete widget.id;
					
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
		widgetUpdate(payload) {
			for (var k in payload) {
				for (var wid in this.widgets) {
					if (this.widgets[wid].id == k) {
						const copy = ObjectUtils.mergeDeep(this.widgets[wid], payload[k]);
						if (payload[k].config)
							copy.config = payload[k].config;
						payload[k] = copy;
						// NOTE(chris): remove internal props
						for (var prop of ['_x', '_y', '_w', '_h', 'index', 'id', 'preset'])
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
					ApiDashboardUser.addWidget(this.dashboard, widget)
				]))
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
		widgetRemove(id) {
			this.$api
				.call(ApiDashboardUser.removeWidget(this.dashboard, id))
				.then(() => {
					this.widgets = this.widgets.filter(widget => widget.id != id);
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		async fetchViewData() {
			let viewDataResult = await this.$api.call(ApiDashboard.getViewData());
			const viewData = viewDataResult.data;
			this.timezone = viewData?.timezone;
			this.userFirstName = viewData?.name;
		}
	},
	async created() {
		this.$p.loadCategory('dashboard');

		await this.fetchViewData();

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

				for (var wid in res.data) {
					let widget = res.data[wid];
					widget.id = wid;
					if (widget.custom || widget.preset) {
						widgets.push(widget);
						this.originalWidgets[wid] = structuredClone(widget);
					} else {
						remove.push(wid);
					}
				}

				remove.forEach(wid => this.widgetRemove(wid));

				this.widgets = widgets;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: /* html */`
	<div class="core-dashboard">
		<h3>
			{{ userFirstName ? $p.t('global/personalGreeting', [ userFirstName ]) : '' }}
			<button
				class="btn ms-2"
				aria-label="edit dashboard"
				v-tooltip="{ showDelay: 1000, value: $p.t('dashboard/edit') }"
				@click="editMode = !editMode"
			><i class="fa-solid fa-gear" aria-hidden="true"></i></button>
		</h3>
		<dashboard-section
			name="general"
			:widgets="widgets"
			@widget-add="widgetAdd"
			@widget-update="widgetUpdate"
			@widget-remove="widgetRemove"
		></dashboard-section>
		<dashboard-widget-picker
			ref="widgetpicker"
			:widgets="widgetsSetup"
		></dashboard-widget-picker>
	</div>`
}
