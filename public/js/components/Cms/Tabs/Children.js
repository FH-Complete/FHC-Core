import ApiCmsAdmin from '../../../api/factory/cmsadmin.js';

export default {
	name: 'CmsChildren',
	props: {
		contentId: Number,
		sprache: String,
		version: Number,
		contentInfo: Object
	},
	emits: ['reload-content-info', 'refresh-tree-node', 'select-content'],
	data() {
		return {
			childs: [],
			possibleChilds: [],
			selectedChild: ''
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
			this.loadChilds();
			this.loadPossibleChilds();
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

		onChildClick(child_content_id) {
			this.$emit('select-content', child_content_id);
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
			<h5>{{ $p.t('cms/folgendeEintraegeSindUntergeordnet') }}</h5>

			<template v-if="childs.length > 0">
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
						<tr v-for="(child, idx) in childs" :key="child.contentchild_id">
							<td class="text-nowrap">
								{{ child.sort }}
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
								<a href="#" @click.prevent="onChildClick(child.child_content_id)">
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
