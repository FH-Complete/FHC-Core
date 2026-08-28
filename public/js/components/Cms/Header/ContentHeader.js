import ApiCmsAdmin from '../../../api/factory/cmsadmin.js';
import VersionSelect from './VersionSelect.js';
import LanguageSelect from './LanguageSelect.js';

export default {
	name: 'CmsContentHeader',
	components: {
		'version-select': VersionSelect,
		'sprache-select': LanguageSelect
	},
	props: {
		contentId: Number,
		sprache: String,
		version: Number,
		contentInfo: Object
	},
	emits: ['select-language-version', 'reload-content-info'],
	data() {
		return {
			titel: '',
			allSprachen: [],
			versionDetails: []
		};
	},
	computed: {
		languages() {
			return this.contentInfo?.languages || [];
		},
		versions() {
			if (!this.contentInfo?.versions || !this.sprache) return [];
			return this.contentInfo.versions[this.sprache] || [];
		},
		availableSprachen() {
			return this.allSprachen.filter(
				s => !this.languages.includes(s.sprache)
			);
		}
	},
	watch: {
		contentId() {
			this.loadTitel();
			this.loadVersionDetails();
		},
		sprache() {
			this.loadTitel();
			this.loadVersionDetails();
		},
		version() {
			this.loadTitel();
		}
	},
	methods: {
		loadTitel() {
			if (this.contentId == null || !this.sprache || !this.version) return;
			this.$api
				.call(ApiCmsAdmin.getContentsprache(this.contentId, this.sprache, this.version))
				.then(result => {
					this.titel = result.data?.titel || '';
				});
		},

		loadVersionDetails() {
			if (this.contentId == null || !this.sprache) {
				this.versionDetails = [];
				return;
			}
			this.$api
				.call(ApiCmsAdmin.getVersions(this.contentId, this.sprache))
				.then(result => {
					this.versionDetails = result.data || [];
				});
		},

		loadSprachen() {
			this.$api
				.call(ApiCmsAdmin.getSprachen())
				.then(result => {
					this.allSprachen = result.data || [];
				});
		},

		selectVersion(ver) {
			this.$emit('select-language-version', this.sprache, ver);
		},

		selectLanguage(lang) {
			const versions = this.contentInfo?.versions?.[lang] || [];
			const maxVersion = versions.length ? Math.max(...versions) : 1;
			this.$emit('select-language-version', lang, maxVersion);
		},

		createVersion() {
			this.$api
				.call(ApiCmsAdmin.postVersion(this.contentId, this.sprache))
				.then(result => {
					const newVersion = result.data.version;
					this.$fhcAlert.alertSuccess(this.$p.t('cms/neueVersionAngelegt'));
					this.$fhcAlert.alertInfo(this.$p.t('cms/neueVersionIstUnsichtbar'));
					this.$emit('reload-content-info');
					this.$emit('select-language-version', this.sprache, newVersion);
				})
				.catch(this.$fhcAlert.handleSystemError);
		},

		createTranslation(newSprache) {
			if (!newSprache) return;
			this.$api
				.call(ApiCmsAdmin.postTranslation(
					this.contentId, this.sprache, this.version, newSprache
				))
				.then(() => {
					this.$fhcAlert.alertSuccess(this.$p.t('cms/uebersetzungAngelegt'));
					this.$emit('reload-content-info');
					this.$emit('select-language-version', newSprache, this.version);
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	mounted() {
		this.loadSprachen();
		this.loadTitel();
		this.loadVersionDetails();
	},
	template: `
		<div class="px-3 pt-2 pb-2 border-bottom" v-if="contentInfo">
			<div class="mb-2 fw-bold">
				{{ $p.t('cms/contentId') }}: {{ contentId }}
				| {{ $p.t('cms/version') }}: {{ version }}
				| {{ $p.t('cms/sprache') }}: {{ sprache }}
				| {{ $p.t('cms/titel') }}: {{ titel }}
			</div>
			<div class="d-flex align-items-center flex-wrap gap-2">
				<div class="d-flex align-items-center gap-2">
					<version-select
						:versions="versions"
						:version-details="versionDetails"
						:version="version"
						@select-version="selectVersion"
					></version-select>
					<sprache-select
						:languages="languages"
						:sprache="sprache"
						@select-language="selectLanguage"
					></sprache-select>
				</div>
				<div>
					<button class="btn btn-sm btn-outline-primary"
						@click="createVersion"
					>{{ $p.t('cms/neueVersionAnlegen') }}</button>
				</div>
				<div class="d-flex align-items-center gap-1">
					<button v-for="s in availableSprachen" :key="s.sprache"
						class="btn btn-sm btn-outline-primary"
						@click="createTranslation(s.sprache)"
					>{{ $p.t('cms/uebersetzungAnlegenIn', [s.sprache]) }}</button>
				</div>
			</div>
		</div>
	`
};
