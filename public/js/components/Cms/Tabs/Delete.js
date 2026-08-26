import ApiCmsAdmin from '../../../api/factory/cmsadmin.js';

export default {
	name: 'CmsDelete',
	props: {
		contentId: Number,
		sprache: String,
		version: Number,
		contentInfo: Object
	},
	emits: ['reload-content-info', 'reload-tree', 'select-content'],
	data() {
		return {
			usages: [],
			loadingUsage: true
		};
	},
	computed: {
		hasRestrict() {
			return this.usages.some(u => u.table !== 'campus.tbl_infoscreen_content');
		},
		// Request to T07: forward defaultLanguage prop to tabs. Using 'German' as fallback.
		isLastVersion() {
			if (!this.contentInfo?.versions || !this.sprache) return false;
			const defaultLang = 'German';
			const versions = this.contentInfo.versions[this.sprache];
			return this.sprache === defaultLang && versions && versions.length === 1;
		},
		totalVersionCount() {
			if (!this.contentInfo?.versions) return 0;
			return Object.values(this.contentInfo.versions)
				.reduce((sum, vers) => sum + vers.length, 0);
		}
	},
	watch: {
		contentId() { this.loadUsage(); }
	},
	methods: {
		loadUsage() {
			if (this.contentId == null) return;
			this.loadingUsage = true;
			this.$api
				.call(ApiCmsAdmin.getUsage(this.contentId))
				.then(result => {
					this.usages = result.data || [];
				})
				.finally(() => { this.loadingUsage = false; });
		},

		deleteVersion() {
			this.$fhcAlert.confirm({
				message: this.$p.t('cms/versionLoeschenBestaetigen'),
				acceptLabel: this.$p.t('ui/loeschen'),
				acceptClass: 'p-button-danger'
			}).then(confirmed => {
				if (!confirmed) return;
				return this.$api.call(
					ApiCmsAdmin.deleteContentsprache(this.contentId, this.sprache, this.version)
				);
			}).then(result => {
				if (!result) return;
				this.$emit('reload-content-info');
			}).catch(this.$fhcAlert.handleSystemError);
		},

		deleteContent() {
			this.$fhcAlert.confirm({
				message: this.$p.t('cms/contentLoeschenBestaetigen', {
					sprachen: this.contentInfo.languages.length,
					versionen: this.totalVersionCount
				}),
				acceptLabel: this.$p.t('ui/loeschen'),
				acceptClass: 'p-button-danger'
			}).then(confirmed => {
				if (!confirmed) return;
				return this.$api.call(ApiCmsAdmin.deleteContent(this.contentId));
			}).then(result => {
				if (!result) return;
				this.$emit('reload-tree');
				this.$nextTick(() => {
					this.$emit('select-content', null);
				});
			}).catch(this.$fhcAlert.handleSystemError);
		}
	},
	mounted() {
		this.loadUsage();
	},
	// Five tables prevent the deletion with ON DELETE RESTRICT. tbl_infoscreen_content
	// deletes its own rows with CASCADE. The legacy code shows neither case and hides the
	// error. See Q5 in the contract.
	template: `
		<div class="p-3" v-if="contentId != null">
			<h5>{{ $p.t('cms/dieseVersionLoeschen') }}</h5>

			<div v-if="isLastVersion" class="alert alert-warning mb-3">
				{{ $p.t('cms/letzteVersionNichtLoeschbar') }}
			</div>
			<div v-else class="mb-3">
				<button class="btn btn-outline-danger" @click="deleteVersion">
					{{ $p.t('cms/dieseVersionLoeschen') }}
				</button>
			</div>

			<hr>

			<h5>{{ $p.t('cms/ganzenContentLoeschen') }}</h5>

			<div v-if="loadingUsage" class="mb-3">
				<div class="spinner-border spinner-border-sm" role="status"></div>
			</div>
			<template v-else>
				<div v-if="usages.length === 0" class="alert alert-info mb-3">
					{{ $p.t('cms/keineVerwendungGefunden') }}
				</div>
				<div v-else class="mb-3">
					<div class="alert alert-warning">
						{{ $p.t('cms/contentWirdVerwendetIn') }}
					</div>
					<ul class="list-group mb-3">
						<li v-for="(u, i) in usages" :key="i"
							class="list-group-item d-flex justify-content-between align-items-center">
							<span>{{ u.label }}</span>
							<span v-if="u.table === 'campus.tbl_infoscreen_content'"
								class="badge bg-danger">
								{{ $p.t('cms/wirdMitgeloescht') }}
							</span>
							<span v-else class="badge bg-warning text-dark">
								{{ $p.t('cms/verhindertLoeschen') }}
							</span>
						</li>
					</ul>
				</div>
			</template>

			<button class="btn btn-danger" @click="deleteContent"
				:disabled="hasRestrict || loadingUsage">
				{{ $p.t('cms/ganzenContentLoeschen') }}
			</button>
		</div>
	`
};
