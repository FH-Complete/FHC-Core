import ApiCmsAdmin from '../../../api/factory/cmsadmin.js';

export default {
	name: 'CmsPermissions',
	props: {
		contentId: Number,
		sprache: String,
		version: Number,
		contentInfo: Object
	},
	emits: ['reload-content-info', 'patch-tree-node', 'select-content'],
	data() {
		return {
			gruppen: [],
			allGruppen: [],
			selectedGruppe: '',
			allGruppenLoaded: false
		};
	},
	computed: {
		availableGruppen() {
			const assigned = new Set(this.gruppen.map(g => g.gruppe_kurzbz));
			return this.allGruppen.filter(g => !assigned.has(g.gruppe_kurzbz));
		}
	},
	watch: {
		contentId() { this.loadGruppen(); }
	},
	methods: {
		loadGruppen() {
			if (this.contentId == null) return Promise.resolve();
			return this.$api
				.call(ApiCmsAdmin.getGruppen(this.contentId))
				.then(result => {
					this.gruppen = result.data || [];
					this.selectedGruppe = '';
				})
				.catch(this.$fhcAlert.handleSystemError);
		},

		// A group only draws the lock icon. It never moves a node, so the tree needs no
		// request here.
		patchTree() {
			this.$emit('patch-tree-node', {
				content_id: this.contentId,
				patch: { groups: this.gruppen.map(g => g.gruppe_kurzbz) }
			});
		},

		loadAllGruppen() {
			if (this.allGruppenLoaded) return;
			this.$api
				.call(ApiCmsAdmin.getAllGruppen())
				.then(result => {
					this.allGruppen = result.data || [];
					this.allGruppenLoaded = true;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},

		async addGruppe() {
			if (!this.selectedGruppe) return;
			try {
				await this.$api.call(
					ApiCmsAdmin.postGruppe(this.contentId, this.selectedGruppe)
				);
				await this.loadGruppen();
				this.patchTree();
				this.$fhcAlert.alertSuccess(this.$p.t('cms/gruppeHinzugefuegt'));
			} catch (e) {
				this.$fhcAlert.handleSystemError(e);
			}
		},

		async removeGruppe(gruppe_kurzbz) {
			if (this.gruppen.length <= 1) {
				const confirmed = await this.$fhcAlert.confirm({
					message: this.$p.t('cms/letzteGruppeEntfernenBestaetigen')
				});
				if (!confirmed) return;
			}
			try {
				await this.$api.call(
					ApiCmsAdmin.deleteGruppe(this.contentId, gruppe_kurzbz)
				);
				await this.loadGruppen();
				this.patchTree();
				this.$fhcAlert.alertSuccess(this.$p.t('cms/gruppeEntfernt'));
			} catch (e) {
				this.$fhcAlert.handleSystemError(e);
			}
		}
	},
	mounted() {
		this.loadAllGruppen();
		this.loadGruppen();
	},
	template: `
		<div class="p-3" v-if="contentId != null">
			<template v-if="gruppen.length > 0">
				<h5>{{ $p.t('cms/folgendeGruppenDuerfenAnsehen') }}</h5>
				<table class="table table-sm table-striped">
					<thead>
						<tr>
							<th>{{ $p.t('cms/gruppe') }}</th>
							<th>{{ $p.t('cms/bezeichnung') }}</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="g in gruppen" :key="g.gruppe_kurzbz">
							<td>{{ g.gruppe_kurzbz }}</td>
							<td>{{ g.bezeichnung }}</td>
							<td>
								<button class="btn btn-sm btn-outline-danger"
									@click="removeGruppe(g.gruppe_kurzbz)">
									{{ $p.t('cms/entfernen') }}
								</button>
							</td>
						</tr>
					</tbody>
				</table>
			</template>

			<div v-else class="alert alert-warning">
				{{ $p.t('cms/seiteDarfVonAllenAngezeigtWerden') }}
			</div>

			<div class="d-flex gap-2 align-items-end mt-3">
				<select class="form-select" v-model="selectedGruppe" style="max-width: 400px;">
					<option value="" disabled></option>
					<option v-for="g in availableGruppen" :key="g.gruppe_kurzbz"
						:value="g.gruppe_kurzbz">
						{{ g.gruppe_kurzbz }} — {{ g.bezeichnung }}
					</option>
				</select>
				<button class="btn btn-primary" @click="addGruppe" :disabled="!selectedGruppe">
					{{ $p.t('cms/hinzufuegen') }}
				</button>
			</div>
		</div>
	`
};
