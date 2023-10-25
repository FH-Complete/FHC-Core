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
			debug_version: 1, // NOTE(chris): switch between tree/treetable and filter/favorites...
			loading: true,
			selected: {}, // NOTE(chris): tree only
			nodes: [],
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
					CoreRESTClient
						.get("components/stv/verband/" + node.data.link)
						.then(result => result.data)
						.then(result => {
							const subNodes = result.map(this.mapResultToTreeData);
							node.children = subNodes;

							let treeitem = this.$refs.tree.$el.querySelector('[data-tree-item-key="' + node.key + '"]');
							if (!this.debug_version) // NOTE(chris): tree only
								treeitem = treeitem.closest('[role="treeitem"]');
							else // NOTE(chris): treetable only
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
						.catch(error => {
							console.error(error);
						});
				}
			}
		},
		onSelectTreeNode(node) {
			if (node.data.link)
				this.$emit('selectVerband', 'components/stv/students/' + node.data.link);
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
		onFilterKeydown(data) {
			// NOTE(chris): tree only
			// TODO(chris): we can online search here
		},
		async filterFav() {
			// NOTE(chris): treetable only
			if (!this.favorites.on && !this.favnodes.length && this.favorites.list.length) {
				this.loading = true;
				this.favnodes = await this.loadNodes(this.favorites.list);
			}
			this.favorites.on = !this.favorites.on;
			CoreRESTClient.post("components/stv/favorites/set", {favorites: JSON.stringify(this.favorites)});
			this.loading = false;
		},
		async loadNodes(links) {
			// NOTE(chris): treetable only
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
				promises.push(CoreRESTClient.get("components/stv/verband/" + (parent == '_' ? '' : parent)).then(res => res.data).then(res => res.filter(node => sortedInParents[parent].includes(node.link + ''))));
			
			// NOTE(chris): merge the resulting arrays and transform them to an associative one
			let result = [].concat.apply([], await Promise.all(promises)).reduce((o, node) => {
				o[node.link + ''] = this.mapResultToTreeData({...node, leaf: true, children: undefined});
				return o;
			}, {});

			return links.map(link => result[link]);
		},
		async markFav(key) {
			// NOTE(chris): treetable only
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
			
			CoreRESTClient.post("components/stv/favorites/set", {favorites: JSON.stringify(this.favorites)});
			// TODO(chris): make clickable with keyboard
		}
	},
	mounted() {
		CoreRESTClient
			.get("components/stv/verband")
			.then(result => result.data)
			.then(result => {
				this.nodes = result.map(this.mapResultToTreeData);
				this.loading = false;
			})
			.catch(error => {
				console.error(error);
			});
		// NOTE(chris): treetable only
		if (this.debug_version)
			CoreRESTClient
				.get("components/stv/favorites")
				.then(result => result.data)
				.then(result => {
					if (result) {
						let f = JSON.parse(result);
						console.log(f);
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
				.catch(error => {
					console.error(error);
				});
	},
	template: `
	<div v-if="!debug_version" class="overflow-auto" tabindex="-1">
		<pv-tree
			ref="tree"
			class="stv-verband p-0"
			v-model:selectionKeys="selected"
			:value="nodes"
			selection-mode="single"
			@node-select="onSelectTreeNode"
			@node-expand="onExpandTreeNode"
			:loading="loading"
			filter
			:pt="{input:{onKeydown:onFilterKeydown}}"
			>
			<template #default="{node}">
				<span :data-tree-item-key="node.key" :title="node.data.studiengang_kz">{{node.label}}</span>
			</template>
		</pv-tree>
	</div>
	<div v-else class="overflow-auto" tabindex="-1">
		<pv-treetable
			ref="tree"
			class="stv-verband p-treetable-sm"
			:value="filteredNodes"
			lazy
			@node-expand="onExpandTreeNode"
			selection-mode="single"
			@node-select="onSelectTreeNode"
			scrollable
			scroll-height="flex"
			>
			<pv-column field="name" header="Verband" expander>
				<template #body="{node}">
					<span :data-tree-item-key="node.key" :title="node.data.studiengang_kz">
						{{node.data.name}}
					</span>
				</template>
			</pv-column>
			<pv-column field="fav" headerStyle="flex: 0 0 auto" style="flex: 0 0 auto">
				<template #header>
					<a href="#" @click.prev="filterFav"><i :class="favorites.on ? 'fa-solid' : 'fa-regular'" class="fa-star"></i></a>
				</template>
				<template #body="{node}">
					<a href="#" @click.prev="markFav(node)"><i :class="favorites.list.includes(node.data.link + '') ? 'fa-solid' : 'fa-regular'" class="fa-star"></i></a>
				</template>
			</pv-column>
		</pv-treetable>
	</div>`
};