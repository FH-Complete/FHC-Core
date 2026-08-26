import ApiCmsAdmin from '../../api/factory/cmsadmin.js';
import ContentTree from './Tree/ContentTree.js';
import ContentHeader from './Header/ContentHeader.js';
import Properties from './Tabs/Properties.js';
import ContentTab from './Tabs/Content.js';
import Permissions from './Tabs/Permissions.js';
import Children from './Tabs/Children.js';
import History from './Tabs/History.js';
import Delete from './Tabs/Delete.js';

const TAB_COMPONENTS = {
	properties: Properties,
	content: ContentTab,
	permissions: Permissions,
	children: Children,
	history: History,
	delete: Delete
};

const TABS = [
	{ key: 'properties', phrase: 'cms/tabEigenschaften' },
	{ key: 'content', phrase: 'cms/tabInhalt' },
	{ key: 'permissions', phrase: 'cms/tabRechte' },
	{ key: 'children', phrase: 'cms/tabChilds' },
	{ key: 'history', phrase: 'cms/tabHistory' },
	{ key: 'delete', phrase: 'cms/tabLoeschen' }
];

export default {
	name: 'CmsAdmin',
	components: {
		'cms-tree': ContentTree,
		'cms-content-header': ContentHeader,
		...TAB_COMPONENTS
	},
	props: {
		cmsRoot: String,
		authUid: String,
		defaultLanguage: { type: String, default: 'German' }
	},
	data() {
		return {
			contentId: null,
			sprache: null,
			version: null,
			tab: 'properties',
			contentInfo: null,
			treeKey: 0
		};
	},
	computed: {
		activeTabComponent() {
			return TAB_COMPONENTS[this.tab] || null;
		}
	},
	watch: {
		'$route.params': {
			handler(params) {
				const id = params.content_id ? Number(params.content_id) : null;
				const sprache = params.sprache || null;
				const version = params.version ? Number(params.version) : null;
				const tab = params.tab || 'properties';

				if (id !== this.contentId || sprache !== this.sprache
					|| version !== this.version || tab !== this.tab) {
					this.contentId = id;
					this.sprache = sprache;
					this.version = version;
					this.tab = tab;

					if (id !== null && this.contentInfo?.content_id !== id) {
						this.loadContentInfo();
					}
				}
			},
			immediate: true
		}
	},
	methods: {
		loadContentInfo() {
			if (this.contentId === null) {
				this.contentInfo = null;
				return;
			}
			this.$api
				.call(ApiCmsAdmin.getContent(this.contentId))
				.then(result => {
					this.contentInfo = result.data;
				});
		},

		selectContent(content_id) {
			if (content_id === null) {
				this.contentId = null;
				this.sprache = null;
				this.version = null;
				this.tab = 'properties';
				this.contentInfo = null;
				this.$router.push({ name: 'index' });
				return;
			}

			this.contentId = content_id;
			this.$api
				.call(ApiCmsAdmin.getContent(content_id))
				.then(result => {
					this.contentInfo = result.data;
					this.preselectLanguageVersion();
					this.pushRoute();
				});
		},

		selectTab(tab) {
			this.tab = tab;
			this.pushRoute();
		},

		selectLanguageVersion(sprache, version) {
			this.sprache = sprache;
			this.version = version;
			this.pushRoute();
		},

		reloadTree() {
			this.treeKey++;
		},

		preselectLanguageVersion() {
			if (!this.contentInfo) return;

			const languages = this.contentInfo.languages || [];
			const versions = this.contentInfo.versions || {};

			let lang = languages.includes(this.defaultLanguage)
				? this.defaultLanguage
				: languages[0] || null;

			let ver = null;
			if (lang && versions[lang] && versions[lang].length) {
				ver = Math.max(...versions[lang]);
			}

			this.sprache = lang;
			this.version = ver;
		},

		pushRoute() {
			if (this.contentId === null) {
				this.$router.push({ name: 'index' });
				return;
			}
			this.$router.push({
				name: 'content',
				params: {
					content_id: this.contentId,
					sprache: this.sprache,
					version: this.version,
					tab: this.tab
				}
			});
		}
	},
	template: `
		<div class="cms-admin d-flex h-100">
			<div class="cms-admin-tree">
				<cms-tree
					:key="treeKey"
					:active-content-id="contentId"
					@select-content="selectContent"
					@entry-created="reloadTree"
				></cms-tree>
			</div>
			<div class="cms-admin-main flex-grow-1 d-flex flex-column" v-if="contentId !== null">
				<cms-content-header
					:content-id="contentId"
					:sprache="sprache"
					:version="version"
					:contentInfo="contentInfo"
					@select-language-version="selectLanguageVersion"
					@reload-content-info="loadContentInfo"
					@reload-tree="reloadTree"
				></cms-content-header>
				<ul class="nav nav-tabs px-3">
					<li class="nav-item" v-for="t in tabs" :key="t.key">
						<a class="nav-link"
							:class="{ active: tab === t.key }"
							href="#"
							@click.prevent="selectTab(t.key)"
						>{{ $p.t(t.phrase) }}</a>
					</li>
				</ul>
				<div class="cms-admin-tab-content flex-grow-1 overflow-auto">
					<component
						:is="activeTabComponent"
						:content-id="contentId"
						:sprache="sprache"
						:version="version"
						:contentInfo="contentInfo"
						@reload-content-info="loadContentInfo"
						@reload-tree="reloadTree"
						@select-content="selectContent"
					></component>
				</div>
			</div>
			<div class="cms-admin-main flex-grow-1 d-flex align-items-center justify-content-center text-muted" v-else>
				{{ $p.t('cms/keinEintragGewaehlt') }}
			</div>
		</div>
	`,
	created() {
		this.tabs = TABS;
	}
};
