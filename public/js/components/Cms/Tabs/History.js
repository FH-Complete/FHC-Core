import ApiCmsAdmin from '../../../api/factory/cmsadmin.js';

export default {
	name: 'CmsHistory',
	props: {
		contentId: Number,
		sprache: String,
		version: Number,
		contentInfo: Object
	},
	emits: ['reload-content-info', 'reload-tree', 'select-content'],
	data() {
		return {
			versions: []
		};
	},
	watch: {
		contentId() { this.loadVersions(); },
		sprache() { this.loadVersions(); }
	},
	methods: {
		loadVersions() {
			if (this.contentId == null || !this.sprache) return;
			this.$api
				.call(ApiCmsAdmin.getVersions(this.contentId, this.sprache))
				.then(result => {
					this.versions = result.data || [];
				})
				.catch(this.$fhcAlert.handleSystemError);
		},

		formatDate(dateStr) {
			if (!dateStr) return '';
			try {
				const dt = luxon.DateTime.fromSQL(dateStr);
				return dt.isValid ? dt.toFormat('dd.MM.yyyy') : dateStr;
			} catch (e) {
				return dateStr;
			}
		}
	},
	mounted() {
		this.loadVersions();
	},
	template: `
		<div class="p-3" v-if="contentId != null">
			<h5>{{ $p.t('cms/versionen') }}</h5>

			<div v-for="v in versions" :key="v.version"
				class="mb-3 p-2 border rounded"
				:class="{ 'border-primary border-2': v.version === version }">
				<div class="fw-bold">
					{{ $p.t('cms/version') }} {{ v.version }}
					<span v-if="!v.sichtbar" class="badge bg-secondary ms-2">
						{{ $p.t('cms/unsichtbar') }}
					</span>
				</div>
				<div>{{ $p.t('cms/erstelltAmVon', { amum: formatDate(v.insertamum), von: v.insertvon }) }}</div>
				<div v-if="v.updatevon">
					{{ $p.t('cms/letzteAenderungVonAm', { von: v.updatevon, amum: formatDate(v.updateamum) }) }}
				</div>
			</div>
		</div>
	`
};
