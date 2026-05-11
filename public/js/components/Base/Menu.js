import MenuEntry from './Menu/Entry.js';

import dragClick from '../../directives/dragClick.js';

import ApiMenu from '../../api/factory/menu.js';

export default {
	components: {
		PvTreetable: primevue.treetable,
		PvColumn: primevue.column,
		MenuEntry
	},
	directives: {
		dragClick
	},
	emits: [
		'selectEntry',
		'drop'
	],
	props: {
		config: {
			type: String,
			required: true,
		},
		preselectedKey: {
			type: String,
			default: null
		}
	},
	data() {
		return {
			loading: true,
			nodes: [],
			selectedKey: [],
			expandedKeys: {},
			filters: {}, // TODO(chris): filter only 1st level?
			favorites: {on: false, list: []}
		}
	},
	computed: {
		filteredNodes() {
			if (this.favorites.on)
				return this.nodes.filter(node => this.favorites.list.includes(node.data.path));
			
			return this.nodes;
		}
	},
	watch: {
		preselectedKey(newVal, oldVal) {
			if (newVal !== oldVal) {
				this.setPreselection();
			}
		}
	},
	methods: {
		reloadNodesWithProp(prop, nodes = undefined) {
			if (!nodes)
				nodes = this.nodes;
			
			nodes.forEach(node => {
				if (node.data[prop]) {
					// reload
					delete node.children;
					this.onExpandTreeNode(node);
				} else if (node.children) {
					this.reloadNodesWithProp(prop, node.children);
				}
			});
		},
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
		async onExpandTreeNode(node) {
			if (!node.children) {
				if (node.data.path) {
					/**
					 * NOTE(chris): activeEl is for keyboard navigation to
					 * prevent the focus jumping down to the next parent
					 * instead of the current submenu entry (which is not yet
					 * loaded)
					 */
					let activeEl = null;
					this.$nextTick(() => {
						this.$nextTick(() => {
							activeEl = document.activeElement;
						});
					});
					this.loading = true;
					
					return this.$api
						.call(ApiMenu.get(this.config, node.data.path))
						.then(result => {
							const subNodes = result.data.map(this.mapResultToTreeData);
							const realNode = this.findNodeByKey(node.key);
							if (realNode)
								realNode.children = subNodes;
							else
								node.children = subNodes; // NOTE(chris): fallback should never be the case

							this.$nextTick(() => {
								if (activeEl != document.activeElement)
									return;

								let treeitem = this.$refs.tree.$el.querySelector('[data-tree-item-key="' + node.key + '"]');
								if (!treeitem)
									return;
								
								treeitem = treeitem.closest('[role="row"]');

								if (!treeitem)
									return;

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
			this.$emit('selectEntry', node.data);
		},
		mapNodesToNoSemReloadNodes(result, node) {
			if (node.data.no_sem_reload)
				result.push(node);
			if (node.children)
				result = node.children.reduce(this.mapNodesToNoSemReloadNodes, result);
			return result;
		},
		mapResultToTreeData(el) {
			const cp = {
				key: ("" + el.path).replace(/\//g, '-'),
				data: el,
				label: el.name // TODO(chris): phrase
			};

			if (el.children)
				cp.children = el.children.map(this.mapResultToTreeData);
			else
				cp.leaf = el.leaf || false;

			return cp;
		},
		async setPreselection()
		{
			if (!this.preselectedKey)
			{
				this.selectedKey = null;
				return;
			}

			let rawKey = this.preselectedKey

			if (!rawKey || typeof rawKey !== 'string')
				return;

			const parts = this.preselectedKey.split('/');
			let currentKey = parts[0];
			let currentNode = this.findNodeByKey(currentKey);

			if (!currentNode)
				return;

			if(this.selectedKey)
			{
				const currentSelectedKey = Object.keys(this.selectedKey).find(Boolean);
				if (currentSelectedKey) {
					if (currentSelectedKey == currentKey)
						return;
					/**
					 * Do not select a new entry if the current is a child of the new one.
					 * This happens if a child entry of a new stg is selected and the router
					 * tries to select the stg root entry (because subtrees do not have
					 * routes yet)
					 */
					const isChild = this.findNodeByKey(
						currentSelectedKey,
						currentNode.children || []
					);
					if (isChild)
						return;
				}
			}

			for (let i = 1; i < parts.length; i++)
			{
				this.expandedKeys[currentNode.key] = true;

				await this.onExpandTreeNode(currentNode);

				currentKey += '-' + parts[i];
				currentNode = this.findNodeByKey(currentKey);

				if (!currentNode)
				{
					return;
				}
			}

			this.selectedKey = {[currentNode.key]: true};
			this.onSelectTreeNode(currentNode);
		},
		async toggleTreeNode(node) {
			if (this.expandedKeys[node.key]) {
				delete this.expandedKeys[node.key];
			} else if (!node.leaf) {
				await this.onExpandTreeNode(node);
				this.expandedKeys[node.key] = true;
			}
		},
		filterFav() {
			this.favorites.on = !this.favorites.on;
			this.$api
				.call(ApiMenu.favorites.set(
					JSON.stringify(this.favorites)
				));
		},
		markFav(key) {
			let index = this.favorites.list.indexOf(key.data.path + '');

			if (index != -1) {
				this.favorites.list.splice(index, 1);
			} else {
				this.favorites.list.push(key.data.path + '');
			}

			this.$api
				.call(ApiMenu.favorites.set(
					JSON.stringify(this.favorites)
				));
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
		this.$api
			.call(ApiMenu.get(this.config))
			.then(result => {
				this.nodes = result.data.map(el => {
					el.root = true;
					return this.mapResultToTreeData(el);
				});
				this.setPreselection();
				this.loading = false;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiMenu.favorites.get())
			.then(result => {
				if (result.data) {
					this.favorites = JSON.parse(result.data);
				}
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: /* html */`
	<pv-treetable
		ref="tree"
		v-model:expanded-keys="expandedKeys"
		v-model:selection-keys="selectedKey"
		class="menu p-treetable-sm"
		:value="filteredNodes"
		selection-mode="single"
		scrollable
		scroll-height="flex"
		:filters="filters"
		@node-expand="onExpandTreeNode"
		@node-select="onSelectTreeNode"
		@focusin="setFavFocus"
		@focusout="unsetFavFocus"
	>
		<pv-column
			field="name"
			expander
			class="text-break"
		>
			<template #header>
				<div class="text-right">
					<div class="p-input-icon-left">
						<i class="pi pi-search"></i>
						<input
							type="text"
							v-model="filters['global']"
							class="form-control ps-5"
							placeholder="Search"
						>
					</div>
				</div>
			</template>
			<template #body="{ node }">
				<menu-entry
					:node="node"
					:data-tree-item-key="node.key"
					v-drag-click="() => toggleTreeNode(node)"
					@drop="$emit('drop', $event)"
				/>
			</template>
		</pv-column>
		<pv-column
			field="fav"
			class="flex-shrink-0 flex-grow-0"
			header-class="flex-shrink-0 flex-grow-0"
		>
			<template #header>
				<a
					v-if="favorites.on || favorites.list.length"
					href="#"
					@click.prevent="filterFav"
				>
					<i
						:class="favorites.on ? 'fa-solid' : 'fa-regular'"
						class="fa-star"
					></i>
				</a>
			</template>
			<template #body="{ node }">
				<a
					v-if="node.data.root"
					href="#"
					tabindex="-1"
					data-link-fav-add
					@click.prevent="markFav(node)"
					@keydown.enter.stop.prevent="markFav(node)"
				>
					<i
						:class="favorites.list.includes(node.data.path + '') ? 'fa-solid' : 'fa-regular'"
						class="fa-star"
					></i>
				</a>
			</template>
		</pv-column>
		<pv-column field="search" class="d-none"></pv-column>
	</pv-treetable>`
};
