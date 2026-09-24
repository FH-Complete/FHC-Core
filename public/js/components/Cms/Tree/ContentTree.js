import ApiCmsAdmin from '../../../api/factory/cmsadmin.js';
import TreeFilter, { emptyCriteria } from './TreeFilter.js';
import MenuSwitch from './MenuSwitch.js';

// The meta dates are SQL timestamps. Format them like the history tab does.
function formatDay(value) {
	if (!value) return '';
	const dt = luxon.DateTime.fromSQL(value);
	return dt.isValid ? dt.toFormat('dd.MM.yyyy') : value;
}

// A day string of the meta value. The inputs deliver YYYY-MM-DD, so compare as text.
function inRange(value, from, to) {
	if (!from && !to) return true;
	if (!value) return false;
	const day = value.substring(0, 10);
	if (from && day < from) return false;
	if (to && day > to) return false;
	return true;
}

// The meta fields that hold a number. The rest hold an SQL timestamp.
const NUMERIC_FIELDS = ['childcount', 'contentlength'];

// Sort key of one node. A content without meta sorts last in a descending list.
function sortValue(meta, contentId, field) {
	const numeric = NUMERIC_FIELDS.indexOf(field) !== -1;
	const m = meta[contentId];
	if (!m) return numeric ? -1 : '';
	if (numeric) return m[field];
	return m[field] || '';
}

const TreeNode = {
	name: 'CmsTreeNode',
	inject: ['treeSelect', 'treeToggle'],
	props: {
		node: Object,
		activeContentId: Number,
		expandedKeys: Object,
		flat: Boolean,
		meta: Object,
		metric: String,
		// Ancestors of this node, from the top down. Only the nodes of the flat result
		// list get one; a child inside the subtree sits under its parent already.
		ancestors: Array,
		// A node that is context rather than a hit. It and its children draw back.
		muted: Boolean
	},
	computed: {
		// Shows the value the active filter or sort works on, so the order is readable.
		badge() {
			if (!this.metric || !this.meta) return null;
			const m = this.meta[this.node.content_id];
			if (!m) return null;
			if (NUMERIC_FIELDS.indexOf(this.metric) !== -1) return String(m[this.metric]);
			return formatDay(m[this.metric]) || null;
		},
		badgeEmpty() {
			return NUMERIC_FIELDS.indexOf(this.metric) !== -1 && this.badge === '0';
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
		// The hit keeps its place; the path above it steps in one level at a time.
		indent() {
			return this.ancestors ? this.ancestors.length : 0;
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
		<li class="py-1" :class="{ 'cms-tree-context': muted }">
			<div v-for="(a, i) in ancestors" :key="a.content_id"
				class="cms-tree-ancestor"
				:style="{ marginLeft: (i * 1.1) + 'em' }">
				<!-- treeSelect reads entitled and content_id, so an ancestor row is a node
				     as far as it is concerned. An ancestor of a unit the editor is not
				     entitled for stays plain text, the same as in the tree itself. -->
				<a v-if="a.entitled" href="#"
					@click.prevent="treeSelect(a)"
				>{{ a.titel || a.content_id }} ({{ a.content_id }})</a>
				<span v-else>{{ a.titel || a.content_id }} ({{ a.content_id }})</span>
			</div>
			<div :style="indent ? { marginLeft: (indent * 1.1) + 'em' } : null">
			<span v-if="!flat && hasChildren"
				style="cursor:pointer;user-select:none"
				@click="treeToggle(node)">
				<i class="fa-solid fa-fw"
					:class="isExpanded ? 'fa-caret-down' : 'fa-caret-right'"></i>
			</span>
			<span v-else-if="!flat" style="display:inline-block;width:1.25em"></span>
			<a v-if="node.entitled"
				href="#"
				class="text-decoration-none"
				:style="labelStyle"
				@click.prevent="treeSelect(node)">{{ node.titel }} ({{ node.content_id }})</a>
			<span v-else :style="!node.aktiv ? 'opacity:0.5' : ''">{{ node.titel }} ({{ node.content_id }})</span>
			<i v-if="node.groups && node.groups.length"
				class="fa-solid fa-lock fa-sm ms-1 cms-tree-lock"
				:title="node.groups.join(', ')"></i>
			<span v-if="badge !== null"
				class="badge ms-1"
				:class="badgeEmpty ? 'bg-light text-muted' : 'bg-secondary'">{{ badge }}</span>
			<ul v-if="!flat && isExpanded && hasChildren" class="list-unstyled ms-3">
				<cms-tree-node
					v-for="child in node.children"
					:key="child.content_id"
					:node="child"
					:active-content-id="activeContentId"
					:expanded-keys="expandedKeys"
					:flat="flat"
					:meta="meta"
					:metric="metric"
					:muted="muted || indent > 0"
				></cms-tree-node>
			</ul>
			</div>
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
			criteria: emptyCriteria(),
			meta: null,
			metaLoading: false,
			// A flat list drops the context a generic title needs. The toggle puts the
			// way down to each hit back, and the choice survives a page switch.
			showHierarchy: localStorage.getItem('cms/treeHierarchie') === '1',
			ancestors: {},
			ancestorsLoading: false
		};
	},
	computed: {
		// The news menu is a flat list without the meta fields.
		advanced() {
			return this.menu === 'content';
		},
		// Every field but the template comes from the meta request.
		needsMeta() {
			const c = this.criteria;
			return c.mine
				|| !!c.updatedFrom || !!c.updatedTo
				|| !!c.createdFrom || !!c.createdTo
				|| c.minChildren !== '' || c.maxChildren !== ''
				|| c.minLength !== '' || c.maxLength !== '' || c.emptyOnly
				|| c.sort === 'updateamum' || c.sort === 'insertamum'
				|| c.sort === 'childcount' || c.sort === 'contentlength';
		},
		narrowing() {
			const c = this.criteria;
			return !!c.template_kurzbz || c.mine
				|| !!c.updatedFrom || !!c.updatedTo
				|| !!c.createdFrom || !!c.createdTo
				|| c.minChildren !== '' || c.maxChildren !== ''
				|| c.minLength !== '' || c.maxLength !== '' || c.emptyOnly;
		},
		active() {
			return this.advanced && (this.narrowing || this.criteria.sort !== '');
		},
		// A hierarchical tree already shows where a node sits. Only a flat list, from a
		// search or from a filter, needs the path spelled out.
		isFlat() {
			return this.active || this.menu === 'news' || !!this.filter;
		},
		showPaths() {
			return this.showHierarchy && this.isFlat;
		},
		// The value the badge shows. The filter drives it, the sort order falls back.
		metric() {
			const c = this.criteria;
			if (!this.active) return null;
			if (c.emptyOnly || c.minLength !== '' || c.maxLength !== '') return 'contentlength';
			if (c.minChildren !== '' || c.maxChildren !== '') return 'childcount';
			if (c.updatedFrom || c.updatedTo) return 'updateamum';
			if (c.createdFrom || c.createdTo) return 'insertamum';
			if (c.sort !== '' && c.sort !== 'titel') return c.sort;
			return null;
		},
		// Flat on purpose: a match sits at any depth, so per-level sorting would hide it.
		visibleNodes() {
			if (!this.active) return this.nodes;

			const flatList = [];
			const walk = (nodes) => {
				for (const node of nodes) {
					flatList.push(node);
					if (node.children && node.children.length) walk(node.children);
				}
			};
			walk(this.nodes);

			return this.sortNodes(flatList.filter(node => this.matches(node)));
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
			// A change moves a date or a child count. Drop the cached meta.
			this.meta = null;
			if (this.needsMeta) this.loadMeta();
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
		matches(node) {
			const c = this.criteria;

			if (c.template_kurzbz && node.template_kurzbz !== c.template_kurzbz) return false;
			if (!this.needsMeta) return true;
			// The meta arrives after the tree. Hide nothing until it is there.
			if (this.meta === null) return true;

			const m = this.meta[node.content_id];
			if (!m) return false;

			if (c.mine && !m.mine) return false;
			if (!inRange(m.updateamum, c.updatedFrom, c.updatedTo)) return false;
			if (!inRange(m.insertamum, c.createdFrom, c.createdTo)) return false;
			if (c.minChildren !== '' && m.childcount < Number(c.minChildren)) return false;
			if (c.maxChildren !== '' && m.childcount > Number(c.maxChildren)) return false;
			if (c.emptyOnly && m.contentlength !== 0) return false;
			if (c.minLength !== '' && m.contentlength < Number(c.minLength)) return false;
			if (c.maxLength !== '' && m.contentlength > Number(c.maxLength)) return false;

			return true;
		},

		// Descending for the numbers and the dates: the largest and the newest come first.
		sortNodes(list) {
			const sort = this.criteria.sort;
			if (!sort) return list;

			if (sort === 'titel') {
				return list.slice().sort(
					(a, b) => (a.titel || '').localeCompare(b.titel || '')
				);
			}

			const meta = this.meta || {};
			return list.slice().sort((a, b) => {
				const va = sortValue(meta, a.content_id, sort);
				const vb = sortValue(meta, b.content_id, sort);
				if (va === vb) return 0;
				return va < vb ? 1 : -1;
			});
		},

		// One request for the whole tree. Cache it, the filter changes often.
		loadMeta() {
			if (this.meta !== null || this.metaLoading) return;

			this.metaLoading = true;
			this.$api
				.call(ApiCmsAdmin.getTreeMeta())
				.then(result => { this.meta = result.data || {}; })
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => { this.metaLoading = false; });
		},

		toggleHierarchy() {
			this.showHierarchy = !this.showHierarchy;
			localStorage.setItem('cms/treeHierarchie', this.showHierarchy ? '1' : '0');

			if (!this.showHierarchy) return;

			// The point of the toggle is the context around a hit, and the subtree is half
			// of it. A collapsed node would show the way down to the hit and nothing under
			// it. Opening the hits leaves every caret free to collapse again.
			for (const node of this.visibleNodes)
				this.expandedKeys[node.content_id] = true;

			this.loadAncestors();
		},

		// One request for the whole list. Asking per node would be a request per hit.
		loadAncestors() {
			if (!this.showPaths) return;

			const ids = this.visibleNodes.map(node => node.content_id);
			if (!ids.length) {
				this.ancestors = {};
				return;
			}

			this.ancestorsLoading = true;
			this.$api
				.call(ApiCmsAdmin.getAncestors(ids))
				.then(result => { this.ancestors = result.data || {}; })
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => { this.ancestorsLoading = false; });
		},

		pathFor(contentId) {
			return this.showPaths ? (this.ancestors[contentId] || []) : null;
		},

		onCriteria(criteria) {
			this.criteria = criteria;
			if (this.needsMeta) this.loadMeta();
			this.loadAncestors();
		},

		onFilter(text) {
			this.filter = text;
			this.loadTree().then(() => { this.loadAncestors(); });
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
	mounted() {
		this.loadTree();
	},
	template: `
		<div class="p-2">
			<menu-switch v-model="menu"></menu-switch>
			<tree-filter class="mt-2"
				:advanced="advanced"
				@filter="onFilter"
				@criteria="onCriteria"></tree-filter>
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
			<div v-if="isFlat && !loading" class="d-flex align-items-center gap-2 mb-2">
				<div v-if="active" class="form-text m-0">
					<i v-if="metaLoading" class="fa-solid fa-spinner fa-spin me-1"></i>
					{{ $p.t('cms/nTreffer', [visibleNodes.length]) }}
				</div>
				<button type="button"
					class="btn btn-sm ms-auto"
					:class="showHierarchy ? 'btn-secondary' : 'btn-outline-secondary'"
					@click="toggleHierarchy">
					<i class="fa-solid fa-fw"
						:class="ancestorsLoading ? 'fa-spinner fa-spin' : 'fa-sitemap'"></i>
					{{ $p.t('cms/hierarchieAnzeigen') }}
				</button>
			</div>
			<div v-if="loading" class="text-center text-muted py-3">
				<i class="fa-solid fa-spinner fa-spin"></i>
			</div>
			<div v-else-if="visibleNodes?.length === 0" class="text-muted py-2">
				{{ (filter || active) ? $p.t('cms/keineTreffer') : $p.t('cms/keineEintraege') }}
			</div>
			<ul v-else class="list-unstyled mb-0">
				<cms-tree-node
					v-for="node in visibleNodes"
					:key="node.content_id"
					:node="node"
					:active-content-id="activeContentId"
					:expanded-keys="expandedKeys"
					:flat="isFlat && !showPaths"
					:meta="meta"
					:metric="metric"
					:ancestors="pathFor(node.content_id)"
				></cms-tree-node>
			</ul>
		</div>
	`
};
