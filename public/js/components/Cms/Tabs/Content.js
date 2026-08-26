import ApiCmsAdmin from '../../../api/factory/cmsadmin.js';
import XsdForm from '../Form/XsdForm.js';
import CmsPreview from '../Preview.js';

export default {
	name: 'CmsContent',
	components: {
		'xsd-form': XsdForm,
		'cms-preview': CmsPreview
	},
	props: {
		contentId: Number,
		sprache: String,
		version: Number,
		contentInfo: Object
	},
	emits: ['reload-content-info', 'reload-tree', 'select-content'],
	data() {
		return {
			schema: null,
			values: {},
			sperre: null,
			sichtbar: true,
			loading: false,
			saving: false
		};
	},
	computed: {
		lockState() {
			if (!this.sperre || this.sperre.gesperrt_uid === null) return 'free';
			if (this.sperre.own) return 'own';
			return 'foreign';
		}
	},
	watch: {
		contentId() { this.loadFormData(); },
		sprache() { this.loadFormData(); },
		version() { this.loadFormData(); }
	},
	methods: {
		loadFormData() {
			if (this.contentId == null || !this.sprache || !this.version) return;
			this.loading = true;
			Promise.all([
				this.$api.call(ApiCmsAdmin.getFormData(this.contentId, this.sprache, this.version)),
				this.$api.call(ApiCmsAdmin.getContentsprache(this.contentId, this.sprache, this.version))
			])
			.then(([formResult, spracheResult]) => {
				const d = formResult.data;
				this.schema = d.schema;
				this.values = d.values || {};
				this.sperre = d.sperre;
				this.sichtbar = !!spracheResult.data.sichtbar;
			})
			.catch(this.$fhcAlert.handleSystemError)
			.finally(() => { this.loading = false; });
		},

		lock() {
			this.$api
				.call(ApiCmsAdmin.postLock(this.sperre.contentsprache_id))
				.then(() => { this.loadFormData(); })
				.catch(this.$fhcAlert.handleSystemError);
		},

		// LEGACY-QUIRK: Releasing a lock releases all locks of this user, not just this page's. See Q1.
		unlock() {
			this.$api
				.call(ApiCmsAdmin.deleteLock(this.sperre.contentsprache_id))
				.then(() => { this.loadFormData(); })
				.catch(this.$fhcAlert.handleSystemError);
		},

		forceUnlock() {
			this.$api
				.call(ApiCmsAdmin.deleteLockForced(this.sperre.contentsprache_id))
				.then(() => { this.loadFormData(); })
				.catch(this.$fhcAlert.handleSystemError);
		},

		save() {
			const freshValues = this.$refs.xsdForm
				? this.$refs.xsdForm.collectValues()
				: this.values;
			this.saving = true;
			this.$api
				.call(ApiCmsAdmin.putFormData(
					this.contentId, this.sprache, this.version, freshValues
				))
				.then(() => {
					this.$fhcAlert.alertSuccess(this.$p.t('cms/gespeichert'));
					if (this.$refs.preview) this.$refs.preview.neuLaden();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => { this.saving = false; });
		}
	},
	mounted() {
		this.loadFormData();
	},
	template: `
		<div class="p-3" v-if="contentId != null">
			<template v-if="!loading && sperre">
				<template v-if="lockState === 'free'">
					<button class="btn btn-primary mb-3" @click="lock">
						{{ $p.t('cms/zurBearbeitungSperren') }}
					</button>
				</template>

				<template v-else-if="lockState === 'own'">
					<div class="alert alert-warning">
						{{ $p.t('cms/freigabeGibtAlleSperrenFrei') }}
					</div>
					<button class="btn btn-outline-secondary mb-3" @click="unlock">
						{{ $p.t('cms/sperreFreigeben') }}
					</button>
					<xsd-form
						ref="xsdForm"
						:schema="schema"
						v-model="values"
						:disabled="false"
					></xsd-form>
					<button class="btn btn-primary mt-2 mb-3" @click="save" :disabled="saving">
						{{ $p.t('cms/speichern') }}
					</button>
				</template>

				<template v-else>
					<div class="alert alert-info">
						{{ $p.t('cms/gesperrtVonSeit', { uid: sperre.gesperrt_uid, start: sperre.start }) }}
					</div>
					<button v-if="sperre.may_force" class="btn btn-danger mb-3" @click="forceUnlock">
						{{ $p.t('cms/freigabeErzwingen') }}
					</button>
				</template>

				<cms-preview
					ref="preview"
					:content-id="contentId"
					:sprache="sprache"
					:version="version"
					:template-kurzbz="contentInfo?.template_kurzbz"
					:sichtbar="sichtbar"
				></cms-preview>
			</template>
		</div>
	`
};
