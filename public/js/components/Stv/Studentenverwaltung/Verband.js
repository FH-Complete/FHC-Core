import {CoreRESTClient} from '../../../RESTClient.js';


import PvTree from "../../../../../index.ci.php/public/js/components/primevue/tree/tree.esm.min.js";
import PvTreetable from "../../../../../index.ci.php/public/js/components/primevue/treetable/treetable.esm.min.js";
import PvColumn from "../../../../../index.ci.php/public/js/components/primevue/column/column.esm.min.js";


export default {
	components: {
		PvTree,
		PvTreetable,
		PvColumn
	},
	emits: [
		'selectVerband'
	],
	data() {
		return {
			loading: true,
			nodes: [],
			selectedKey: [],
			filters: {}, // TODO(chris): filter only 1st level?
			favnodes: [],
			favorites: {on: false, list: []}
		}
	},
	computed: {
		filteredNodes() {
			// TODO(chris): what to display actually?
			return this.favorites.on ? this.favnodes : this.nodes;
		}
	},
	methods: {
		findNodeByKey(key, arr) {
			if (!arr)
				arr = this.nodes;
			let res = arr.filter(n => n.key == key);
			if (res.length)
				return res.pop();
			res = arr.map(n => n.children ? this.findNodeByKey(key, n.children) : null).filter(a => a);
			if (res.length)
				return res.pop();
			return null;
		},
		onExpandTreeNode(node) {
			if (!node.children) {
				if (node.data.link) {
					let activeEl = null;
					this.$nextTick(() => {
						this.$nextTick(() => {
							activeEl = document.activeElement;
						});
					});
					this.loading = true;
					
					this.$fhcApi
						.get('api/frontend/v1/stv/verband/' + node.data.link)
						.then(result => result.data)
						.then(result => {
							const subNodes = result.map(this.mapResultToTreeData);
							const realNode = this.findNodeByKey(node.key);
							if (realNode)
								realNode.children = subNodes;
							else
								node.children = subNodes; // NOTE(chris): fallback should never be the case

							let treeitem = this.$refs.tree.$el.querySelector('[data-tree-item-key="' + node.key + '"]');
							treeitem = treeitem.closest('[role="row"]');
							
							this.$nextTick(() => {
								if (activeEl == document.activeElement)
									treeitem.dispatchEvent(new KeyboardEvent('keydown', {
										code: 'ArrowDown',
										key: 'ArrowDown'
									}));
							});

							this.loading = false;
						})
						.catch(this.$fhcAlert.handleSystemError);
				}
			}
		},
		onSelectTreeNode(node) {
			if (node.data.link)
				this.$emit('selectVerband', {link: node.data.link, studiengang_kz: node.data.stg_kz});
		},
		mapResultToTreeData(el) {
			const cp = {
				key: ("" + el.link).replace('/', '-'),
				data: el,
				label: el.name
			};

			if (el.children)
				cp.children = el.children.map(this.mapResultToTreeData);
			else
				cp.leaf = el.leaf || false;

			return cp;
		},
		async filterFav() {
			if (!this.favorites.on && !this.favnodes.length && this.favorites.list.length) {
				this.loading = true;
				this.favnodes = await this.loadNodes(this.favorites.list);
			}
			this.favorites.on = !this.favorites.on;
			this.$fhcApi
				.factory.stv.verband.favorites.set(JSON.stringify(this.favorites));
			this.loading = false;
		},
		async loadNodes(links) {
			let sortedInParents = links.reduce((o, link) => {
				link = link + '';
				let parent,
					parts = link.split('/');
				if (parts.length == 1) {
					parent = '_';
				} else {
					parts.pop();
					parent = parts.join('/');
				}
				if (!o[parent])
					o[parent] = [link];
				else
					o[parent].push(link);
				return o;
			}, {});
			
			let promises = [];
			for (let parent in sortedInParents)
				promises.push(
					this.$fhcApi
						.get('api/frontend/v1/stv/verband/' + (parent == '_' ? '' : parent))
						.then(res => res.data)
						.then(res => res.filter(node => sortedInParents[parent].includes(node.link + '')))
				);
			
			// NOTE(chris): merge the resulting arrays and transform them to an associative one
			let result = [].concat.apply([], await Promise.all(promises)).reduce((o, node) => {
				o[node.link + ''] = this.mapResultToTreeData({...node, leaf: true, children: undefined});
				return o;
			}, {});

			return links.map(link => result[link]);
		},
		async markFav(key) {
			let index = this.favorites.list.indexOf(key.data.link + '');

			if (index != -1) {
				if (this.favnodes.length)
					this.favnodes = this.favnodes.filter(node => node.data.link != key.data.link);
				this.favorites.list.splice(index, 1);
			} else {
				if (this.favnodes.length || this.favorites.on)
					this.favnodes.push((await this.loadNodes([key.data.link])).pop());
				this.favorites.list.push(key.data.link + '');
			}
			
			this.$fhcApi
				.factory.stv.verband.favorites.set(JSON.stringify(this.favorites));
		},
		unsetFavFocus(e) {
			if (e.target.dataset?.linkFavAdd !== undefined) {
				e.target.tabIndex = -1;
			} else {
				let items = e.target.querySelectorAll('[data-link-fav-add]:not([tabindex="-1"])');
				items.forEach(el => el.tabIndex = document.activeElement == el ? 0 : -1);
			}
		},
		setFavFocus(e) {
			if (e.target.dataset?.linkFavAdd !== undefined) {
				e.target.tabIndex = 0;
			} else {
				let items = e.target.querySelectorAll('[data-link-fav-add][tabindex="-1"]');
				items.forEach(el => el.tabIndex = 0);
			}
		}
	},
	mounted() {
		this.$fhcApi
			.factory.stv.verband.get()
			.then(result => {
				this.nodes = result.data.map(this.mapResultToTreeData);
				this.loading = false;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$fhcApi
			.factory.stv.verband.favorites.get()
			.then(result => {
				if (result.data) {
					let f = JSON.parse(result.data);
					if (f.on) {
						this.loading = true;
						this.favorites = f;
						this.loadNodes(this.favorites.list).then(res => {
							this.favnodes = res;
							this.loading = false;
						});
					} else
						this.favorites = f;
				}
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="overflow-auto" tabindex="-1">
		<pv-treetable
			ref="tree"
			class="stv-verband p-treetable-sm"
			:value="filteredNodes"
			@node-expand="onExpandTreeNode"
			selection-mode="single"
			v-model:selection-keys="selectedKey"
			@node-select="onSelectTreeNode"
			scrollable
			scroll-height="flex"
			@focusin="setFavFocus"
			@focusout="unsetFavFocus"
			:filters="filters"
			>
			<pv-column field="name" expander>
				<template #header>
					<div class="text-right">
						<div class="p-input-icon-left">
							<i class="pi pi-search"></i>
							<input type="text" v-model="filters['global']" class="form-control ps-5" placeholder="Search" />
						</div>
					</div>
				</template>
				<template #body="{node}">
					<span :data-tree-item-key="node.key" :title="node.data.studiengang_kz">
						{{node.data.name}}
					</span>
				</template>
			</pv-column>
			<pv-column field="fav" headerStyle="flex: 0 0 auto" style="flex: 0 0 auto">
				<template #header>
					<a href="#" @click.prevent="filterFav"><i :class="favorites.on ? 'fa-solid' : 'fa-regular'" class="fa-star"></i></a>
				</template>
				<template #body="{node, column}">
					<a
						href="#"
						@click.prevent="markFav(node)"
						@keydown.enter.stop.prevent="markFav(node)"
						tabindex="-1"
						data-link-fav-add
						>
						<i :class="favorites.list.includes(node.data.link + '') ? 'fa-solid' : 'fa-regular'" class="fa-star"></i>
					</a>
				</template>
			</pv-column>
			<pv-column field="studiengang_kz" class="d-none"></pv-column>
		</pv-treetable>
	</div>`
};