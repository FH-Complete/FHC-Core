import ApiCmsAdmin from '../../../api/factory/cmsadmin.js';
import TreeFilter from './TreeFilter.js';
import MenuSwitch from './MenuSwitch.js';

const TreeNode = {
	name: 'CmsTreeNode',
	inject: ['treeSelect', 'treeToggle'],
	props: {
		node: Object,
		activeContentId: Number,
		expandedKeys: Object,
		flat: Boolean
	},
	computed: {
		hasChildren() {
			return this.node.children && this.node.children.length > 0;
		},
		isExpanded() {
			return !!this.expandedKeys[this.node.content_id];
		},
		isActive() {
			return this.activeContentId != null
				&& this.node.content_id === this.activeContentId;
		},
		labelStyle() {
			const s = {};
			if (this.isActive) {
				s.fontWeight = 'bold';
				s.background = '#e8f0fe';
				s.borderRadius = '3px';
				s.padding = '0 4px';
			}
			if (!this.node.aktiv) {
				s.opacity = '0.5';
			}
			return s;
		}
	},
	template: `
		<li class="py-1">
			<span v-if="!flat && hasChildren"
				style="cursor:pointer;user-select:none"
				@click="treeToggle(node)">
				<i class="fa-solid fa-fw"
					:class="isExpanded ? 'fa-caret-down' : 'fa-caret-right'"></i>
			</span>
			<span v-else-if="!flat" style="display:inline-block;width:1.25em"></span>
			<i v-if="node.groups && node.groups.length"
				class="fa-solid fa-lock fa-sm me-1"
				:title="node.groups.join(', ')"></i>
			<a v-if="node.entitled"
				href="#"
				class="text-decoration-none"
				:style="labelStyle"
				@click.prevent="treeSelect(node)">{{ node.titel }} ({{ node.content_id }})</a>
			<span v-else :style="!node.aktiv ? 'opacity:0.5' : ''">{{ node.titel }} ({{ node.content_id }})</span>
			<ul v-if="!flat && isExpanded && hasChildren" class="list-unstyled ms-3">
				<cms-tree-node
					v-for="child in node.children"
					:key="child.content_id"
					:node="child"
					:active-content-id="activeContentId"
					:expanded-keys="expandedKeys"
					:flat="flat"
				></cms-tree-node>
			</ul>
		</li>
	`
};

export default {
	name: 'CmsTree',
	components: {
		'cms-tree-node': TreeNode,
		'tree-filter': TreeFilter,
		'menu-switch': MenuSwitch
	},
	props: {
		activeContentId: Number
	},
	emits: ['select-content', 'entry-created'],
	data() {
		const stored = localStorage.getItem('cms/menu');
		return {
			menu: (stored === 'content' || stored === 'news') ? stored : 'content',
			filter: '',
			nodes: [],
			loading: false,
			expandedKeys: {}
		};
	},
	provide() {
		return {
			treeSelect: (node) => { this.selectNode(node); },
			treeToggle: (node) => { this.toggleNode(node); }
		};
	},
	watch: {
		menu() {
			this.loadTree();
		},
		activeContentId(id) {
			if (id != null) {
				this.expandPathTo(id);
			}
		}
	},
	methods: {
		loadTree() {
			this.loading = true;
			this.$api
				.call(ApiCmsAdmin.getTree(this.menu, this.filter))
				.then(result => {
					
					this.nodes = result.data;
					this.expandedKeys = {};
					if (this.activeContentId != null) {
						this.expandPathTo(this.activeContentId);
					}
					this.loading = false;
				});
		},
		expandPathTo(targetId) {
			const search = (nodes) => {
				for (const node of nodes) {
					if (node.content_id === targetId) return true;
					if (node.children && node.children.length) {
						if (search(node.children)) {
							this.expandedKeys[node.content_id] = true;
							return true;
						}
					}
				}
				return false;
			};
			search(this.nodes);
		},
		toggleNode(node) {
			if (this.expandedKeys[node.content_id]) {
				delete this.expandedKeys[node.content_id];
			} else {
				this.expandedKeys[node.content_id] = true;
			}
		},
		selectNode(node) {
			if (node.entitled) {
				this.$emit('select-content', node.content_id);
			}
		},
		onFilter(text) {
			this.filter = text;
			this.loadTree();
		},
		createEntry(parentId) {
			this.$api
				.call(ApiCmsAdmin.postContent(parentId))
				.then(result => {
					const newId = result.data.content_id;
					this.$emit('entry-created');
					this.$emit('select-content', newId);
				});
		}
	},
	mounted() {
		this.loadTree();
	},
	template: `
		<div class="p-2">
			<menu-switch v-model="menu"></menu-switch>
			<tree-filter class="mt-2" @filter="onFilter"></tree-filter>
			<div class="mt-2 mb-2">
				<button class="btn btn-sm btn-outline-secondary me-1"
					@click="createEntry(null)">
					{{ $p.t('cms/neuenEintragHinzufuegen') }}
				</button>
				<button class="btn btn-sm btn-outline-secondary"
					:disabled="activeContentId == null"
					@click="createEntry(activeContentId)">
					{{ $p.t('cms/neuenChildEintragHinzufuegen') }}
				</button>
			</div>
			<div v-if="loading" class="text-center text-muted py-3">
				<i class="fa-solid fa-spinner fa-spin"></i>
			</div>
			<div v-else-if="nodes?.length === 0" class="text-muted py-2">
				{{ filter ? $p.t('cms/keineTreffer') : $p.t('cms/keineEintraege') }}
			</div>
			<ul v-else class="list-unstyled mb-0">
				<cms-tree-node
					v-for="node in nodes"
					:key="node.content_id"
					:node="node"
					:active-content-id="activeContentId"
					:expanded-keys="expandedKeys"
					:flat="menu === 'news'"
				></cms-tree-node>
			</ul>
		</div>
	`
};
