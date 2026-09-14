export default {
	name: "Compat",
	props: {
		mode: { type: String, required: true },
		path: { type: String, required: true },
		query_string: { type: String, required: true, default: ''}
	},
	data: function() {
		return {
			lastLoadediFrameURL: '',
			srcUrl: ''
		};
	},
	computed: {
		propsWatchHelper: function() {
			return this.mode + '#' + this.path + '#' + this.query_string;
		}
	},
	mounted: function() {
		this.srcUrl = this.buildSrcUrl();
	},
	watch: {
		propsWatchHelper: function() {
			let currentiFrameURL = this.$refs.compatiframe ? this.$refs.compatiframe.src : '';

			console.log('currentiFrameURL: ' + currentiFrameURL);
			console.log('lastLoadediFrameURL: ' + this.lastLoadediFrameURL);

			let url = this.buildSrcUrl();
			if(this.lastLoadediFrameURL !== url) {
				this.srcUrl = url;
			}
		}
	},
	methods: {
		buildSrcUrl: function() {
			console.log('srcUrl begin: ' + this.path);

			let url = false;
			switch(this.mode) {
				case 'ci':
					url = FHC_JS_DATA_STORAGE_OBJECT.app_root + 'index.ci.php/' + this.path;
					break;
				case 'legacy':
					url = FHC_JS_DATA_STORAGE_OBJECT.app_root + this.path;
					break;
				default:
					url = false;
			}
			if(this.query_string !== '' && url) {
				url += '?' + this.query_string;
			}

			console.log('srcUrl end: ' + url);
			return url;
		},
		loadHandler: function() {
			console.log('loadHandler');
			console.log(JSON.stringify(this.$refs.compatiframe.contentWindow.location));

			let iframe_href = this.$refs.compatiframe.contentWindow.location.href;
			let ci_urlstart = FHC_JS_DATA_STORAGE_OBJECT.app_root + 'index.ci.php/';
			let legacy_urlstart = FHC_JS_DATA_STORAGE_OBJECT.app_root;
			let routerpath = null;

			this.lastLoadediFrameURL = iframe_href;

			console.log('iframe_href: ' + iframe_href);
			console.log('ci_urlstart: ' + ci_urlstart);
			console.log('legacy_url_start: ' + legacy_urlstart);

			if(iframe_href.startsWith(ci_urlstart)) {
				routerpath = iframe_href.replace(
					ci_urlstart, '/Cis/Compat/ci/');
			} else if(iframe_href.startsWith(legacy_urlstart)) {
				routerpath = iframe_href.replace(
					legacy_urlstart, '/Cis/Compat/legacy/');
			} else {
				return;
			}

			console.log(routerpath);

			if(this.$route.fullPath !== routerpath) {
				this.$router.push(routerpath);
			}
		}
	},
	template: `
		<div class="w-100">
			<iframe
				ref="compatiframe"
				v-if="srcUrl"
				:src="srcUrl"
				@load="loadHandler"
				style="width:100%; height:90vh; border:0; display:block;"
			></iframe>
		<div v-else class="alert alert-warning">Keine URL gefunden.</div>
		</div>
	`
};
