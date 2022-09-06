import CoreDashboardItem from './Dashboard/Item.js';
import DragAndDrop from './Dashboard/DragAndDrop.js';

export default {
	components: {
		CoreDashboardItem,
		DragAndDrop
	},
	data: () => ({
		widgetCache: {},
		componentCache: {},
		widgetWizard: null,
		allowedWidgets: [],
		widgets: [],
		name: '',
		newMode: false,
		editMode: false
	}),
	computed: {
		loaded: function() {
			return this.widgets && this.name;
		}
	},
	props: [
		"dashboard"
	],
	methods: {
		saveConfig() {
			// TODO(chris): SAVE!
			console.log('SAVE', this.widgets);
			this.editMode = false;
		},
		startWidgetWizard() {
			// TODO(chris): load widgets!
			let self = this;
			axios.get('/fhcomplete/index.ci.php/api/v1/dashboard/Dashboard/Widgets', {
				headers: {
					'FHC-API-KEY':'itservice@technikum-wien.at'
				},
				params: {
					dashboard: this.dashboard
				}
			}).then(function(result) {console.log(result);
				self.allowedWidgets = result.data;
			});
			this.widgetWizard.show();
		},
		newWidget(widget) {
			let self = this;
			let newWidget = [widget.widget_id, this.widgets.length, this.widgets.length, this.widgets.length, []];
			// TODO(chris): loadingscreen?
			(new Promise(function (resolve, reject) {
				if (self.widgetCache[widget.widget_id]) {
					resolve(self.widgetCache[widget.widget_id]);
				} else {
					this.extendWidgetAndCache(widget).then(widget => {
						resolve(widget);
					});
				}
			})).then(widget => {
				newWidget[5] = widget;
				self.widgets.push(newWidget);
				self.widgetWizard.hide();
			});
		},
		removeWidget(id) {
			if (confirm('Are you sure you want to delete this widget?'))
				this.widgets = this.widgets.filter(widget => widget[0] != id);
		},
		getWidget(widget_id) {
			let self = this;
			return new Promise(function(resolve, reject) {
				if (self.widgetCache[widget_id])
					return resolve(self.widgetCache[widget_id]);
				axios.get('/fhcomplete/index.ci.php/api/v1/dashboard/User/Widget', {
					headers: {
						'FHC-API-KEY':'itservice@technikum-wien.at'
					},
					params: {
						widget_id: widget_id
					}
				})
				.then(result => self.extendWidgetAndCache(result.data))
				.then(widget => resolve(widget));
			});
		},
		extendWidgetAndCache(widget) {
			let self = this;
			return new Promise(function(resolve, reject) {
				let name = widget.component_name;
				widget.arguments = JSON.parse(widget.arguments);
				widget.component_name = widget.component_name.replace(/[A-Z]/g, (m,o) => (o > 0 ? "-" : "") + m.toLowerCase());
				self.getComponent(name, widget.component_path).then(component => {
					widget.component = component;
					return widget;
				}).then(() => {
					self.widgetCache[widget.widget_id] = widget;
					resolve(self.widgetCache[widget.widget_id]);
				});
			});
		},
		getComponent(name, path) {
			let self = this;
			return new Promise(async function(resolve, reject) {
				if (self.componentCache[name])
					return resolve(self.componentCache[name]);


				self.componentCache[name] = (await import(path)).default;
				resolve(self.componentCache[name]);
			});
		}
	},
	mounted() {
		let self = this;
		this.widgetWizard = new bootstrap.Modal(this.$refs.widgetWizard);
		axios.get('/fhcomplete/index.ci.php/api/v1/dashboard/User/AuthObj', {
			headers: {
				'FHC-API-KEY':'itservice@technikum-wien.at'
			}
		}).then(function(result) {
			self.name = result.data.name;
		});
		axios.get('/fhcomplete/index.ci.php/api/v1/dashboard/User/Widgets', {
			headers: {
				'FHC-API-KEY':'itservice@technikum-wien.at'
			},
			params: {
				dashboard: this.dashboard
			}
		}).then(function(result) {
			let promises = [];
			result.data.forEach(function(item) {
				promises.push(new Promise(function(resolve, reject) {
					self.getWidget(item[0]).then(function(widget) {
						item[5] = widget;
						resolve();
					});
				}));
			});
			Promise.all(promises).then(function() {
				self.widgets = result.data;
			})
		});
	},
	template: `<div class="core-dashboard">
		<div style="width:500px">
			<drag-and-drop width="4" height="3" :items="[{id:0,c:'blue',x:1,y:1,w:1,h:1},{id:1,c:'red',x:2,y:1,w:1,h:2},{id:2,c:'green',x:1,y:3,w:2,h:1}]"></drag-and-drop>
		</div>
		<h3 v-if="loaded" class="d-flex">
			<span class="col">Hallo {{name}}!</span>

			<!--div class="dropstart">
				<button type="button" class="btn btn-secondary" data-bs-display="static" data-bs-auto-close="outside" data-bs-toggle="dropdown" aria-expanded="false">
					<i class="fa-solid fa-gear"></i>
				</button>
				<ul class="dropdown-menu">
					<li><span class="dropdown-item-text">Meine Widgets</span></li>
					<li v-for="widget in widgets" :key="widget[0]">
						<button class="dropdown-item" type="button" data-bs-toggle="collapse" :data-bs-target="'#settings-' + widget[0]">Action</button>
						<core-dashboard-item :id="'settings-' + widget[0]" class="collapse w-100" editMode="true" :config="widget[4]" @change="v => widget[4] = v" :widget="widget[5]" @remove="removeWidget(widget[0])"></core-dashboard-item>
					</li>
				</ul>
			</div-->

			<a href="#" class="link-secondary" v-if="editMode" @click.prevent="saveConfig"><i class="fa-solid fa-floppy-disk"></i></a>
			<a href="#" class="link-secondary" v-else @click.prevent="editMode = true"><i class="fa-solid fa-gear"></i></a>
		</h3>
		<div v-else class="fetch-loader">Loading...</div>
		<div v-if="loaded" class="core-dashboard-list row">
			<core-dashboard-item v-for="widget in widgets" :key="widget[0]" :editMode="editMode" :config="widget[4]" @change="v => widget[4] = v" :widget="widget[5]" :style="{'--core-dashboard-order-sm':widget[1],'--core-dashboard-order-md':widget[2],'--core-dashboard-order-lg':widget[3]}" @remove="removeWidget(widget[0])"></core-dashboard-item>
			<div v-if="editMode" class="core-dashboard-item-add col-sm-6 col-md-3">
				<div class="fixed-h fixed-h-1">
					<div class="card d-flex justify-content-center align-items-center" @click="startWidgetWizard">
						<i class="fa-solid fa-plus h1"></i>
					</div>
				</div>
			</div>
		</div>
		<div ref="widgetWizard" class="modal fade" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Add new widget</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class="row">
							<template v-if="allowedWidgets.length">
								<div v-for="widget in allowedWidgets" :v-key="widget.dashboard_widget_id" class="col">
									<div class="card" @click="newWidget(widget)">
									  	<img src="..." class="card-img-top" alt="...">
										<div class="card-body">
											<h5 class="card-title text-center">{{widget.name}}</h5>
										</div>
									</div>
								</div>
							</template>
							<div v-else class="fetch-loader">Loading...</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>`
}
