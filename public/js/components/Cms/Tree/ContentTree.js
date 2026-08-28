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
		flat: Boolean,
		clicks: Object
	},
	computed: {
		clickCount() {
			return this.clicks ? (this.clicks[this.node.content_id] || 0) : null;
		},
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
			<span v-if="clickCount !== null"
				class="badge ms-1"
				:class="clickCount ? 'bg-secondary' : 'bg-light text-muted'">{{ clickCount }}</span>
			<ul v-if="!flat && isExpanded && hasChildren" class="list-unstyled ms-3">
				<cms-tree-node
					v-for="child in node.children"
					:key="child.content_id"
					:node="child"
					:active-content-id="activeContentId"
					:expanded-keys="expandedKeys"
					:flat="flat"
					:clicks="clicks"
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
	emits: ['select-content'],
	data() {
		const stored = localStorage.getItem('cms/menu');
		return {
			menu: (stored === 'content' || stored === 'news') ? stored : 'content',
			filter: '',
			nodes: [],
			loading: false,
			expandedKeys: {},
			clicks: null,
			clicksSince: null,
			ranked: false,
			clicksLoading: false,
			months: 12,
			loadedMonths: null
		};
	},
	computed: {
		// Flat on purpose: a relic sits at any depth, so per-level sorting would hide it.
		visibleNodes() {
			if (!this.ranked || !this.clicks) return this.nodes;

			const flatList = [];
			const walk = (nodes) => {
				for (const node of nodes) {
					flatList.push(node);
					if (node.children && node.children.length) walk(node.children);
				}
			};
			walk(this.nodes);

			return flatList.slice().sort(
				(a, b) => (this.clicks[b.content_id] || 0) - (this.clicks[a.content_id] || 0)
			);
		}
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
		// Keeps the expanded branches. A reload must not fold the tree back up.
		loadTree() {
			this.loading = true;
			return this.$api
				.call(ApiCmsAdmin.getTree(this.menu, this.filter))
				.then(result => {
					this.nodes = result.data;
					if (this.activeContentId != null) {
						this.expandPathTo(this.activeContentId);
					}
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => { this.loading = false; });
		},

		// --- Public: the parent calls these instead of rebuilding the component ---

		reload() {
			return this.loadTree();
		},

		// Replaces one branch. Use it after a change to the children, the title, the
		// active flag or the organisational unit of that content.
		refreshNode(contentId) {
			// The news menu is a flat list of its own. Only a full reload fits it.
			if (this.menu === 'news' || contentId == null) return this.loadTree();

			const slot = this.findSlot(this.nodes, contentId);
			// Not in the tree, so the change may add a new root. Rebuild.
			if (slot === null) return this.loadTree();

			return this.$api
				.call(ApiCmsAdmin.getSubtree(contentId))
				.then(result => {
					if (result.data) {
						slot.list.splice(slot.index, 1, result.data);
					} else {
						slot.list.splice(slot.index, 1);
					}
					if (this.activeContentId != null) {
						this.expandPathTo(this.activeContentId);
					}
				})
				.catch(this.$fhcAlert.handleSystemError);
		},

		// Writes known fields straight into one node. Groups do not change the structure,
		// so they need no request.
		patchNode(contentId, patch) {
			const slot = this.findSlot(this.nodes, contentId);
			if (slot === null) return;
			Object.assign(slot.node, patch);
		},

		findSlot(nodes, contentId) {
			for (let i = 0; i < nodes.length; i++) {
				if (nodes[i].content_id === contentId) {
					return { list: nodes, index: i, node: nodes[i] };
				}
				if (nodes[i].children && nodes[i].children.length) {
					const found = this.findSlot(nodes[i].children, contentId);
					if (found !== null) return found;
				}
			}
			return null;
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
		toggleRanking() {
			if (this.ranked) {
				this.ranked = false;
				return;
			}
			this.loadClicks();
		},

		// A wider period costs a longer query, so cache the counts per period.
		loadClicks() {
			if (this.clicks && this.loadedMonths === this.months) {
				this.ranked = true;
				return;
			}
			this.clicksLoading = true;
			this.$api
				.call(ApiCmsAdmin.getClickCounts(this.months))
				.then(result => {
					this.clicks = result.data.counts || {};
					this.clicksSince = result.data.since;
					this.loadedMonths = result.data.months;
					this.ranked = true;
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => { this.clicksLoading = false; });
		},

		onMonthsChange() {
			if (this.ranked || this.clicks) this.loadClicks();
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
					this.$fhcAlert.alertSuccess(this.$p.t('cms/eintragAngelegt'));
					// A new root is not under any branch, so only a reload shows it.
					const refreshed = parentId == null
						? this.loadTree()
						: this.refreshNode(parentId);
					// Select after the node exists, or expandPathTo finds nothing.
					return refreshed.then(() => {
						this.$emit('select-content', newId);
					});
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	created() {
		this.monthOptions = [3, 6, 12, 24, 0];
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
			<div class="mb-2">
				<div class="input-group input-group-sm">
					<button class="btn"
						:class="ranked ? 'btn-secondary' : 'btn-outline-secondary'"
						:disabled="clicksLoading"
						@click="toggleRanking">
						<i class="fa-solid fa-fw"
							:class="clicksLoading ? 'fa-spinner fa-spin' : 'fa-arrow-down-9-1'"></i>
						{{ ranked ? $p.t('cms/hierarchieZeigen') : $p.t('cms/nachKlicksReihen') }}
					</button>
					<select class="form-select"
						v-model.number="months"
						:disabled="clicksLoading"
						:title="$p.t('cms/zeitraum')"
						@change="onMonthsChange">
						<option v-for="m in monthOptions" :key="m" :value="m">
							{{ m ? $p.t('cms/letzteNMonate', [m]) : $p.t('cms/gesamterZeitraum') }}
						</option>
					</select>
				</div>
				<div v-if="loadedMonths !== null" class="form-text mt-1">
					{{ clicksSince
						? $p.t('cms/klicksSeit', [clicksSince.substring(0, 10)])
						: $p.t('cms/klicksGesamt') }}
				</div>
			</div>
			<div v-if="loading" class="text-center text-muted py-3">
				<i class="fa-solid fa-spinner fa-spin"></i>
			</div>
			<div v-else-if="visibleNodes?.length === 0" class="text-muted py-2">
				{{ filter ? $p.t('cms/keineTreffer') : $p.t('cms/keineEintraege') }}
			</div>
			<ul v-else class="list-unstyled mb-0">
				<cms-tree-node
					v-for="node in visibleNodes"
					:key="node.content_id"
					:node="node"
					:active-content-id="activeContentId"
					:expanded-keys="expandedKeys"
					:flat="ranked || menu === 'news'"
					:clicks="clicks"
				></cms-tree-node>
			</ul>
		</div>
	`
};
