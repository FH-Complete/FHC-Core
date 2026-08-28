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
	emits: ['reload-content-info', 'refresh-tree-node', 'select-content'],
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
			if (this.sperre.expired) return 'expired';
			return 'foreign';
		},
		lockInfo() {
			if (!this.sperre || this.sperre.gesperrt_uid === null) return '';

			const start = this.formatDateTime(this.sperre.start);
			const ende = this.formatDateTime(this.sperre.expires);

			if (this.sperre.own)
				return this.$p.t('cms/eigeneSperre', [start, ende]);

			const uid = this.sperre.gesperrt_uid;
			if (this.sperre.expired)
				return this.$p.t('cms/gesperrtVonSeitAbgelaufen', [uid, start, ende]);
			return this.$p.t('cms/gesperrtVonSeit', [uid, start, ende]);
		}
	},
	watch: {
		contentId() { this.loadFormData(); },
		sprache() { this.loadFormData(); },
		version() { this.loadFormData(); }
	},
	methods: {
		formatDateTime(dateStr) {
			if (!dateStr) return this.$p.t('cms/unbekannt');
			const dt = luxon.DateTime.fromSQL(dateStr);
			return dt.isValid ? dt.toFormat('dd.MM.yyyy HH:mm') : dateStr;
		},

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
					// The form holds the title, and the tree shows it.
					this.$emit('refresh-tree-node', this.contentId);
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
				<div v-if="sperre.gesperrt_uid !== null"
					class="alert"
					:class="lockState === 'expired' ? 'alert-warning' : 'alert-info'">
					{{ lockInfo }}
				</div>

				<template v-if="lockState === 'free'">
					<button class="btn btn-primary mb-3" @click="lock">
						{{ $p.t('cms/zurBearbeitungSperren') }}
					</button>
				</template>

				<template v-else-if="lockState === 'own'">
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

				<template v-else-if="lockState === 'expired'">
					<button class="btn btn-warning mb-3" @click="lock">
						{{ $p.t('cms/sperreUebernehmen') }}
					</button>
				</template>

				<template v-else>
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
