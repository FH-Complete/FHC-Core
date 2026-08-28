import ApiCmsAdmin from '../../api/factory/cmsadmin.js';
import ContentTree from './Tree/ContentTree.js';
import ContentHeader from './Header/ContentHeader.js';
import Properties from './Tabs/Properties.js';
import ContentTab from './Tabs/Content.js';
import Permissions from './Tabs/Permissions.js';
import Children from './Tabs/Children.js';
import History from './Tabs/History.js';
import Delete from './Tabs/Delete.js';
import HorizontalSplit from '../horizontalsplit/horizontalsplit.js';

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
		'horizontal-split': HorizontalSplit,
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
			pending: 0
		};
	},
	computed: {
		activeTabComponent() {
			return TAB_COMPONENTS[this.tab] || null;
		},
		// Two switches in flight can resolve out of order, so block the view during one.
		busy() {
			return this.pending > 0;
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

					if (id === null) return;

					if (this.contentInfo?.content_id !== id) {
						this.loadContentInfo().then(() => { this.applyRouteDefaults(); });
					} else {
						this.applyRouteDefaults();
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
				return Promise.resolve();
			}
			this.pending++;
			return this.$api
				.call(ApiCmsAdmin.getContent(this.contentId))
				.then(result => {
					this.contentInfo = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => { this.pending--; });
		},

		// Language, version and tab are optional in the address. Fill in what the address
		// leaves out, and replace a value that the content does not have. Then write the
		// complete address back, so a reload and a copied link both work.
		applyRouteDefaults() {
			if (this.contentId === null || !this.contentInfo) return;

			const languages = this.contentInfo.languages || [];
			const versions = this.contentInfo.versions || {};

			let lang = this.sprache;
			if (lang === null || !languages.includes(lang)) {
				lang = languages.includes(this.defaultLanguage)
					? this.defaultLanguage
					: languages[0] || null;
			}

			const langVersions = (lang && versions[lang]) ? versions[lang] : [];
			let ver = this.version;
			if (ver === null || !langVersions.includes(ver)) {
				ver = langVersions.length ? Math.max(...langVersions) : null;
			}

			if (lang === this.sprache && ver === this.version) return;

			this.sprache = lang;
			this.version = ver;
			// Replace, or the short address stays in the history and the back button
			// walks through both forms of the same page.
			this.pushRoute(true);
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

			// Set id, language and version in one flush, or the tabs query the new content
			// with the version of the old one.
			this.pending++;
			this.$api
				.call(ApiCmsAdmin.getContent(content_id))
				.then(result => {
					this.contentId = content_id;
					this.contentInfo = result.data;
					this.preselectLanguageVersion();
					this.pushRoute();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => { this.pending--; });
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

		// Three grades of tree update. Pick the cheapest one that stays correct.
		// A full reload drops the filter, the expanded branches and the click ranking,
		// so keep it for changes that move nodes between branches.
		reloadTree() {
			if (this.$refs.tree) this.$refs.tree.reload();
		},

		refreshTreeNode(contentId) {
			if (this.$refs.tree) this.$refs.tree.refreshNode(contentId);
		},

		patchTreeNode(payload) {
			if (this.$refs.tree) this.$refs.tree.patchNode(payload.content_id, payload.patch);
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

		pushRoute(replace = false) {
			if (this.contentId === null) {
				this.$router.push({ name: 'index' });
				return;
			}

			// The optional parts are positional. Stop at the first missing one, or the
			// address carries an empty segment.
			const params = { content_id: this.contentId };
			if (this.sprache !== null) {
				params.sprache = this.sprache;
				if (this.version !== null) {
					params.version = this.version;
					params.tab = this.tab;
				}
			}

			const target = { name: 'content', params };
			if (replace) {
				this.$router.replace(target);
			} else {
				this.$router.push(target);
			}
		}
	},
	template: `
		<div class="cms-admin h-100">
			<horizontal-split :default-ratio="[25, 75]">
			<template #left>
				<cms-tree
					ref="tree"
					:active-content-id="contentId"
					@select-content="selectContent"
				></cms-tree>
			</template>
			<template #right>
			<div class="cms-admin-main h-100 d-flex flex-column" v-if="contentId !== null">
				<cms-content-header
					:content-id="contentId"
					:sprache="sprache"
					:version="version"
					:contentInfo="contentInfo"
					@select-language-version="selectLanguageVersion"
					@reload-content-info="loadContentInfo"
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
						@refresh-tree-node="refreshTreeNode"
						@patch-tree-node="patchTreeNode"
						@select-content="selectContent"
					></component>
				</div>
			</div>
			<div class="cms-admin-main h-100 d-flex align-items-center justify-content-center text-muted" v-else>
				{{ $p.t('cms/keinEintragGewaehlt') }}
			</div>
			</template>
			</horizontal-split>

			<div v-if="busy" class="cms-admin-overlay">
				<div class="spinner-border text-primary" role="status">
					<span class="visually-hidden">{{ $p.t('ui/loading') }}</span>
				</div>
			</div>
		</div>
	`,
	created() {
		this.tabs = TABS;
	}
};
