import ApiCmsAdmin from '../../../api/factory/cmsadmin.js';

export default {
	name: 'CmsHierarchy',
	props: {
		contentId: Number,
		sprache: String,
		version: Number,
		contentInfo: Object
	},
	emits: ['reload-content-info', 'reload-tree', 'refresh-tree-node', 'select-content'],
	data() {
		return {
			parents: [],
			childs: [],
			possibleChilds: [],
			selectedChild: '',
			dragIndex: null,
			orderBefore: null,
			dropped: false,
			savingOrder: false
		};
	},
	computed: {
		// tbl_contentchild has no unique index. The server permits a duplicate assignment.
		// Therefore the select list hides the children that are already assigned.
		availableChilds() {
			const assigned = new Set(this.childs.map(c => c.child_content_id));
			return this.possibleChilds.filter(c => !assigned.has(c.content_id));
		}
	},
	watch: {
		contentId() { this.loadAll(); },
		sprache() { this.loadAll(); }
	},
	methods: {
		loadAll() {
			this.loadParents();
			this.loadChilds();
			this.loadPossibleChilds();
		},

		loadParents() {
			if (this.contentId == null || !this.sprache) return;
			this.$api
				.call(ApiCmsAdmin.getParents(this.contentId, this.sprache))
				.then(result => { this.parents = result.data || []; })
				.catch(this.$fhcAlert.handleSystemError);
		},

		loadChilds() {
			if (this.contentId == null || !this.sprache) return;
			this.$api
				.call(ApiCmsAdmin.getChilds(this.contentId, this.sprache))
				.then(result => { this.childs = result.data || []; })
				.catch(this.$fhcAlert.handleSystemError);
		},

		loadPossibleChilds() {
			if (this.contentId == null || !this.sprache) return;
			this.$api
				.call(ApiCmsAdmin.getPossibleChilds(this.contentId, this.sprache))
				.then(result => {
					this.possibleChilds = result.data || [];
					this.selectedChild = '';
				})
				.catch(this.$fhcAlert.handleSystemError);
		},

		childLabel(c) {
			const titel = c.titel || this.$p.t('cms/ohneTitel');
			return titel + ' (' + c.content_id + ')';
		},

		async addChild() {
			if (!this.selectedChild) return;
			try {
				await this.$api.call(
					ApiCmsAdmin.postChild(this.contentId, this.selectedChild)
				);
				this.loadAll();
				// Only this branch changes, so refresh this branch.
				this.$emit('refresh-tree-node', this.contentId);
				this.$fhcAlert.alertSuccess(this.$p.t('cms/childHinzugefuegt'));
			} catch (e) {
				this.$fhcAlert.handleSystemError(e);
			}
		},

		async removeChild(contentchild_id) {
			const confirmed = await this.$fhcAlert.confirm({
				message: this.$p.t('cms/zuordnungEntfernenBestaetigen')
			});
			if (!confirmed) return;
			try {
				await this.$api.call(ApiCmsAdmin.deleteChild(contentchild_id));
				this.loadAll();
				this.$emit('refresh-tree-node', this.contentId);
				this.$fhcAlert.alertSuccess(this.$p.t('cms/childEntfernt'));
			} catch (e) {
				this.$fhcAlert.handleSystemError(e);
			}
		},

		// Detaches this content from one parent. That is the same row as a child
		// assignment, seen from the other end, so it uses the same endpoint.
		async removeParent(contentchild_id) {
			const confirmed = await this.$fhcAlert.confirm({
				message: this.$p.t('cms/zuordnungEntfernenBestaetigen')
			});
			if (!confirmed) return;
			try {
				await this.$api.call(ApiCmsAdmin.deleteChild(contentchild_id));
				this.loadParents();
				// The content leaves one branch and may become a root, so rebuild the tree.
				this.$emit('reload-tree');
				this.$fhcAlert.alertSuccess(this.$p.t('cms/childEntfernt'));
			} catch (e) {
				this.$fhcAlert.handleSystemError(e);
			}
		},

		async moveSort(contentchild_id, direction) {
			try {
				await this.$api.call(
					ApiCmsAdmin.putChildSort(contentchild_id, direction)
				);
				this.loadChilds();
				this.$emit('refresh-tree-node', this.contentId);
				this.$fhcAlert.alertSuccess(this.$p.t('cms/sortierungGespeichert'));
			} catch (e) {
				this.$fhcAlert.handleSystemError(e);
			}
		},

		// --- Drag and drop ---
		// The dragged row follows the cursor: every row it enters swaps places with it.
		// The drop then only has to write the order that already stands on screen.

		onDragStart(index, event) {
			this.dragIndex = index;
			this.dropped = false;
			this.orderBefore = this.childs.map(c => c.contentchild_id);
			event.dataTransfer.effectAllowed = 'move';
			// Firefox starts no drag without a payload.
			event.dataTransfer.setData('text/plain', String(index));
		},

		onDragEnter(index) {
			if (this.dragIndex === null || this.dragIndex === index) return;
			const moved = this.childs.splice(this.dragIndex, 1)[0];
			this.childs.splice(index, 0, moved);
			this.dragIndex = index;
		},

		onDrop() {
			this.dropped = true;
		},

		onDragEnd() {
			if (this.dragIndex === null) return;

			const before = this.orderBefore;
			this.dragIndex = null;
			this.orderBefore = null;

			// Escape cancels the drag. Put the rows back.
			if (!this.dropped) {
				this.applyOrder(before);
				return;
			}
			if (this.orderDiffers(before)) this.saveOrder(before);
		},

		applyOrder(contentchild_ids) {
			if (!contentchild_ids) return;
			const byId = new Map(this.childs.map(c => [c.contentchild_id, c]));
			this.childs = contentchild_ids.map(id => byId.get(id)).filter(c => c);
		},

		orderDiffers(contentchild_ids) {
			if (!contentchild_ids) return false;
			return this.childs.some((c, i) => c.contentchild_id !== contentchild_ids[i]);
		},

		// One request for the whole order. A child moves over any distance, and the arrow
		// endpoint swaps two neighbours, so it would need one request per position.
		async saveOrder(before) {
			this.savingOrder = true;
			try {
				await this.$api.call(ApiCmsAdmin.putChildOrder(
					this.contentId,
					this.childs.map(c => c.contentchild_id)
				));
				this.loadChilds();
				this.$emit('refresh-tree-node', this.contentId);
				this.$fhcAlert.alertSuccess(this.$p.t('cms/sortierungGespeichert'));
			} catch (e) {
				this.applyOrder(before);
				this.$fhcAlert.handleSystemError(e);
			} finally {
				this.savingOrder = false;
			}
		},

		onContentClick(content_id) {
			this.$emit('select-content', content_id);
		},

		isFirst(index) {
			return index === 0;
		},

		isLast(index) {
			return index === this.childs.length - 1;
		}
	},
	mounted() {
		this.loadAll();
	},
	template: `
		<div class="p-3" v-if="contentId != null">
			<h5>{{ $p.t('cms/folgendeEintraegeSindUebergeordnet') }}</h5>

			<table v-if="parents.length > 0" class="table table-sm table-striped">
				<thead>
					<tr>
						<th>ID</th>
						<th>{{ $p.t('cms/titel') }}</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="parent in parents" :key="parent.contentchild_id">
						<td>{{ parent.content_id }}</td>
						<td>
							<a href="#" @click.prevent="onContentClick(parent.content_id)">
								{{ parent.titel || $p.t('cms/ohneTitel') }}
							</a>
						</td>
						<td>
							<button class="btn btn-sm btn-outline-danger"
								@click="removeParent(parent.contentchild_id)">
								{{ $p.t('cms/entfernen') }}
							</button>
						</td>
					</tr>
				</tbody>
			</table>

			<div v-else class="text-muted">
				{{ $p.t('cms/keineUebergeordnetenEintraege') }}
			</div>

			<h5 class="mt-4">{{ $p.t('cms/folgendeEintraegeSindUntergeordnet') }}</h5>

			<template v-if="childs.length > 0">
				<div class="form-text mb-1">
					<i v-if="savingOrder" class="fa-solid fa-spinner fa-spin me-1"></i>
					{{ $p.t('cms/ziehenZumSortieren') }}
				</div>
				<table class="table table-sm table-striped">
					<thead>
						<tr>
							<th>{{ $p.t('cms/sortierung') }}</th>
							<th>ID</th>
							<th>{{ $p.t('cms/titel') }}</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(child, idx) in childs" :key="child.contentchild_id"
							draggable="true"
							:style="dragIndex === idx ? 'opacity:0.4' : ''"
							@dragstart="onDragStart(idx, $event)"
							@dragenter.prevent="onDragEnter(idx)"
							@dragover.prevent
							@drop.prevent="onDrop"
							@dragend="onDragEnd">
							<td class="text-nowrap">
								<i class="fa-solid fa-grip-vertical text-muted me-1"
									style="cursor:grab"
									:title="$p.t('cms/ziehenZumSortieren')"></i>
								{{ idx + 1 }}
								<button class="btn btn-sm btn-link p-0 ms-1"
									:disabled="isFirst(idx)"
									@click="moveSort(child.contentchild_id, 'up')">
									<i class="fa-solid fa-arrow-up"></i>
								</button>
								<button class="btn btn-sm btn-link p-0 ms-1"
									:disabled="isLast(idx)"
									@click="moveSort(child.contentchild_id, 'down')">
									<i class="fa-solid fa-arrow-down"></i>
								</button>
							</td>
							<td>{{ child.child_content_id }}</td>
							<td>
								<a href="#" draggable="false"
									@click.prevent="onContentClick(child.child_content_id)">
									{{ child.titel || $p.t('cms/ohneTitel') }}
								</a>
							</td>
							<td>
								<button class="btn btn-sm btn-outline-danger"
									@click="removeChild(child.contentchild_id)">
									{{ $p.t('cms/entfernen') }}
								</button>
							</td>
						</tr>
					</tbody>
				</table>
			</template>

			<div v-else class="text-muted">
				{{ $p.t('cms/keineUntereintraege') }}
			</div>

			<div class="d-flex gap-2 align-items-end mt-3">
				<select class="form-select" v-model="selectedChild" style="max-width: 500px;">
					<option value="" disabled></option>
					<option v-for="c in availableChilds" :key="c.content_id"
						:value="c.content_id">
						{{ childLabel(c) }}
					</option>
				</select>
				<button class="btn btn-primary" @click="addChild" :disabled="!selectedChild">
					{{ $p.t('cms/hinzufuegen') }}
				</button>
			</div>
		</div>
	`
};
