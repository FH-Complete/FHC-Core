export default {
	name: 'CmsPreview',
	props: {
		contentId: Number,
		sprache: String,
		version: Number,
		templateKurzbz: String,
		sichtbar: Boolean
	},
	computed: {
		// The preview uses the legacy renderer cms/content.php. It reads xslt_xhtml, which
		// holds a complete HTML document for all eleven templates. The CIS4 route reads
		// xslt_xhtml_c4, where four templates hold a whole document instead of a fragment.
		previewUrl() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root
				+ 'cms/content.php?content_id=' + this.contentId
				+ '&version=' + this.version
				+ '&sprache=' + this.sprache
				+ '&sichtbar=';
		},
		// The CIS4 route takes content_id only. Its Vue route is
		// /CisVue/Cms/Content/:content_id, and extra segments hit the catch-all, which
		// redirects to the dashboard. CIS4 picks the version itself and the language from
		// the viewer profile.
		cis4Url() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root
				+ FHC_JS_DATA_STORAGE_OBJECT.ci_router
				+ '/CisVue/Cms/content/' + this.contentId;
		},
		// The redirect template navigates away with JavaScript. In an iframe this can close
		// the admin page. Therefore this case uses a link and not an iframe.
		isRedirect() {
			return this.templateKurzbz === 'redirect';
		}
	},
	methods: {
		neuLaden() {
			if (this.$refs.previewFrame) {
				this.$refs.previewFrame.src = this.previewUrl + '&t=' + Date.now();
			}
		}
	},
	template: `
		<div class="mt-3">
			<div v-if="sichtbar === false" class="text-danger fw-bold mb-2">
				{{ $p.t('cms/unsichtbarImLivesystem') }}
			</div>
			<div class="mb-2">
				<a :href="cis4Url" target="_blank">{{ $p.t('cms/inCis4Ansehen') }}</a>
			</div>
			<template v-if="isRedirect">
				<a :href="previewUrl" target="_blank">
					{{ $p.t('cms/vorschauInEigenemFenster') }}
				</a>
			</template>
			<template v-else>
				<iframe
					ref="previewFrame"
					:src="previewUrl"
					style="width: 100%; min-height: 500px; border: 1px solid #ccc;"
				></iframe>
			</template>
		</div>
	`
};
