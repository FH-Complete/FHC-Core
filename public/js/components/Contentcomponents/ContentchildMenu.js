import ApiCms from '../../api/factory/cms.js';

/**
 * Content component: lists the child pages of a content as a menu.
 *
 * It replaces a hand written box of content.php links. Those links carry a fixed
 * content_id and break when a page moves. This list follows tbl_contentchild, which the
 * Kindelemente tab of the CMS admin already maintains.
 *
 * Without the content-id property the menu shows the children of the page it sits on.
 * With it, the menu shows the children of another page. The second form exists because
 * the same menu appears on several pages today.
 */
export default {
	name: 'ContentchildMenu',
	inject: {
		contentcomponentContentId: { default: null }
	},
	props: {
		contentId: {
			type: [Number, String],
			default: null
		}
	},
	data() {
		return {
			kinder: [],
			loading: true,
			failed: false
		};
	},
	computed: {
		sprache() {
			return this.$p.user_language.value;
		},
		// The property wins. Without it the menu takes the page it sits on.
		// Vue unwraps an injected ref on the instance, so no .value here.
		quelle() {
			return this.contentId || this.contentcomponentContentId;
		}
	},
	methods: {
		load() {
			if (!this.quelle)
			{
				this.loading = false;
				return;
			}

			this.loading = true;
			this.failed = false;
			this.$api
				.call(ApiCms.getContentChilds(this.quelle, this.sprache))
				.then(res => { this.kinder = res.data || []; })
				.catch(() => { this.failed = true; })
				.finally(() => { this.loading = false; });
		}
	},
	watch: {
		quelle() { this.load(); },
		// The titles come per language, so a language switch needs a new request.
		sprache() { this.load(); }
	},
	created() {
		this.load();
	},
	template: /*html*/ `
		<div class="fhc-contentcomponent-contentchildmenu">
			<div v-if="loading" class="text-muted">...</div>
			<div v-else-if="failed" class="alert alert-warning py-2">
				Das Menü konnte nicht geladen werden.
			</div>
			<!-- An empty menu renders nothing. A reader has no use for the note that a
			     page has no children, and the group rule may have removed every entry. -->
			<ul v-else-if="kinder.length" class="list-unstyled mb-0">
				<li v-for="kind in kinder" :key="kind.content_id" class="mb-1">
					<router-link :to="{ name: 'Content', params: { content_id: kind.content_id } }">
						{{ kind.titel }}
					</router-link>
				</li>
			</ul>
		</div>
	`
};
