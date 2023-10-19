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
			nodes: []
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
		markFav(key) {
			// NOTE(chris): treetable only
			// TODO(chris): IMPLEMENT
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
			:value="nodes"
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
			<pv-column field="fav" header="FAV" headerStyle="flex: 0 0 auto" style="flex: 0 0 auto">
				<template #body="{node}">
					<i :class="node.data.fav ? 'fa-solid' : 'fa-regular'" class="fa-star" @click="markFav(node.key)"></i>
				</template>
			</pv-column>
		</pv-treetable>
	</div>`
};