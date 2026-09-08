// Safety net for the spinner.
const CIS4_LOAD_TIMEOUT = 10000;

// Cis.js reads window.innerWidth and switches to its mobile layout below 767px. In a frame
// that is the width of the frame, so these widths drive the real layout and not a zoom.
// Its resize listener reacts on its own, which means no reload and no second long wait.
const CIS4_WIDTHS = {
	mobile: 375,
	desktop: null
};

export default {
	name: 'CmsPreview',
	props: {
		contentId: Number,
		sprache: String,
		version: Number,
		templateKurzbz: String,
		// The legacy renderer runs no Vue, so it draws nothing for a contentcomponent
		// marker. The CIS4 view mounts them, which is the whole point of switching.
		hasContentcomponents: Boolean,
		// False for a template whose xslt_xhtml_c4 is a copy of the legacy stylesheet.
		// Such a template emits a whole HTML document, and the CIS4 renderer writes that
		// into a div, so the layout is lost. Defaults true, so an older payload warns
		// about nothing.
		hasCis4Stylesheet: { type: Boolean, default: true },
		sichtbar: Boolean
	},
	data() {
		// The legacy renderer stays the default, so nothing changes until an editor asks
		// for the other one. The choice survives a page switch, like the menu switch.
		const stored = localStorage.getItem('cms/previewMode');
		const mode = (stored === 'legacy' || stored === 'cis4') ? stored : 'legacy';
		const width = localStorage.getItem('cms/previewWidth');
		return {
			mode: mode,
			// True while the split handle is dragged. See onSplitterDown.
			dragging: false,
			cis4Width: CIS4_WIDTHS.hasOwnProperty(width) ? width : 'desktop',
			// The CIS4 shell pulls its whole stack before it shows anything, which takes a
			// while. Start covered when the frame is the one on screen.
			cis4Loading: mode === 'cis4'
		};
	},
	watch: {
		mode(value) {
			if (value === 'cis4') this.startCis4Load();
			else this.stopCis4Poll();
		},
		// A new content, language or version reloads the frame.
		cis4PreviewUrl() {
			if (this.mode === 'cis4') this.startCis4Load();
		}
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
		// The second preview renders the content the way CIS4 does: the route loads the
		// CIS4 shell, which brings its stylesheets, its Vue app and the contentcomponents.
		// CISVUE-Header sets the body class in-frame by itself, and Cis.css hides the CIS4
		// header for it, so the frame shows the content and no navigation.
		// version travels as a route segment, because Cms/Content.js already declares that
		// prop and the route passes props. sprache and preview travel as query parameters,
		// because neither is a prop there.
		cis4PreviewUrl() {
			let url = FHC_JS_DATA_STORAGE_OBJECT.app_root
				+ FHC_JS_DATA_STORAGE_OBJECT.ci_router
				+ '/CisVue/Cms/content/' + this.contentId;

			if (this.version !== null)
				url += '/' + this.version;

			url += '?preview=1';

			if (this.sprache)
				url += '&sprache=' + encodeURIComponent(this.sprache);

			return url;
		},
		// The overlay sits on the wrapper, so the wrapper carries the width. Otherwise the
		// spinner would cover the whole pane while the frame is only 375px wide.
		cis4WrapStyle() {
			const px = CIS4_WIDTHS[this.cis4Width];
			// content-box on purpose: the frame reads its own window.innerWidth, and with
			// the usual border-box the border would eat two pixels. The preset is meant
			// to be the width the CIS4 app sees, so it must not depend on the frame.
			return px === null
				? ''
				: 'width: ' + px + 'px; box-sizing: content-box; max-width: 100%;'
					+ ' margin: 0 auto;';
		},
		// The redirect template navigates away with JavaScript. In an iframe this can close
		// the admin page. Therefore this case uses a link and not an iframe.
		isRedirect() {
			return this.templateKurzbz === 'redirect';
		}
	},
	methods: {
		selectMode(value) {
			localStorage.setItem('cms/previewMode', value);
			this.mode = value;
		},

		// The split handle sits right next to the frame. As soon as the pointer crosses
		// into the frame, the frame document takes the mousemove and the mouseup, the
		// listeners HorizontalSplit put on the parent window never fire, and the drag
		// stops halfway. Turning pointer events off in the frame for the length of the
		// drag lets those events through to the page underneath.
		//
		// Capture phase on purpose: HorizontalSplit.dragStart calls stopPropagation, so a
		// listener that waits for the event to bubble back up never sees it at all.
		onSplitterDown(event) {
			const target = event.target;
			if (target && target.closest && target.closest('.horizontalsplitter'))
				this.dragging = true;
		},

		onSplitterUp() {
			this.dragging = false;
		},

		selectWidth(value) {
			localStorage.setItem('cms/previewWidth', value);
			this.cis4Width = value;

			// A frame resized by its parent gets no resize event of its own, so Cis.js
			// would keep the window width it read at load and stay on that layout. Same
			// origin, so tell the frame. That spares a reload of the whole CIS4 stack.
			this.$nextTick(() => {
				try {
					const frame = this.$refs.cis4Frame;
					const win = frame && frame.contentWindow;
					if (win) win.dispatchEvent(new win.Event('resize'));
				} catch (e) {
					// Not reachable. The frame keeps the layout it loaded with.
				}
			});
		},

		startCis4Load() {
			this.stopCis4Poll();
			this.cis4Loading = true;
		},

		// The frame fires load once the CIS4 shell is there, but the shell then fetches the
		// content over the API, so the load event alone clears the spinner too early and
		// leaves the placeholder text on screen. The frame is same origin, so wait for the
		// element the CIS4 renderer writes once it holds the content.
		onCis4Load() {
			this.stopCis4Poll();
			const started = Date.now();

			this.cis4Poll = setInterval(() => {
				let done = false;

				try {
					const doc = this.$refs.cis4Frame && this.$refs.cis4Frame.contentDocument;
					done = !!(doc && doc.getElementById('fhc-cms-content'));
				} catch (e) {
					// Not readable after all. Stop waiting rather than spin forever.
					done = true;
				}

				if (done || Date.now() - started > CIS4_LOAD_TIMEOUT) {
					this.stopCis4Poll();
					this.cis4Loading = false;
				}
			}, 120);
		},

		stopCis4Poll() {
			if (this.cis4Poll) {
				clearInterval(this.cis4Poll);
				this.cis4Poll = null;
			}
		},

		neuLaden() {
			if (this.$refs.previewFrame) {
				this.$refs.previewFrame.src = this.previewUrl + '&t=' + Date.now();
			}
			if (this.$refs.cis4Frame) {
				this.startCis4Load();
				this.$refs.cis4Frame.src = this.cis4PreviewUrl + '&t=' + Date.now();
			}
		}
	},
	mounted() {
		// On the window, not the document: HorizontalSplit ends the drag from a listener
		// it puts on the window, and a mouseup aimed at the window never reaches a
		// listener on the document. The window is the first node of the capture path, so
		// it sees everything the document would and that case as well.
		window.addEventListener('mousedown', this.onSplitterDown, true);
		window.addEventListener('mouseup', this.onSplitterUp, true);
		// Backstop. A missed mouseup would leave the frame unclickable.
		window.addEventListener('blur', this.onSplitterUp);
	},
	beforeUnmount() {
		window.removeEventListener('mousedown', this.onSplitterDown, true);
		window.removeEventListener('mouseup', this.onSplitterUp, true);
		window.removeEventListener('blur', this.onSplitterUp);
		this.stopCis4Poll();
	},
	template: `
		<div class="mt-3" :class="{ 'cms-preview-dragging': dragging }">
			<div v-if="sichtbar === false" class="text-danger fw-bold mb-2">
				{{ $p.t('cms/unsichtbarImLivesystem') }}
			</div>
			<div class="mb-2">
				<a :href="cis4Url" target="_blank">{{ $p.t('cms/inCis4Ansehen') }}</a>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2 mb-2">
				<div class="btn-group btn-group-sm" role="group">
					<button type="button" class="btn"
						:class="mode === 'legacy' ? 'btn-primary' : 'btn-outline-secondary'"
						@click="selectMode('legacy')"
					>{{ $p.t('cms/vorschauLegacy') }}</button>
					<button type="button" class="btn"
						:class="mode === 'cis4' ? 'btn-primary' : 'btn-outline-secondary'"
						@click="selectMode('cis4')"
					>{{ $p.t('cms/vorschauCis4') }}</button>
				</div>

				<div v-if="mode === 'cis4' && !isRedirect"
					class="btn-group btn-group-sm" role="group">
					<button type="button" class="btn"
						:class="cis4Width === 'desktop' ? 'btn-primary' : 'btn-outline-secondary'"
						:title="$p.t('cms/breiteDesktop')"
						@click="selectWidth('desktop')"
					><i class="fa-solid fa-display"></i></button>
					<button type="button" class="btn"
						:class="cis4Width === 'mobile' ? 'btn-primary' : 'btn-outline-secondary'"
						:title="$p.t('cms/breiteMobil')"
						@click="selectWidth('mobile')"
					><i class="fa-solid fa-mobile-screen-button"></i></button>
				</div>
			</div>

			<template v-if="mode === 'legacy'">
				<div v-if="hasContentcomponents" class="alert alert-info py-2">
					{{ $p.t('cms/contentcomponentsNurCis4') }}
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
			</template>

			<template v-else>
				<div v-if="!hasCis4Stylesheet" class="alert alert-warning py-2">
					{{ $p.t('cms/keinCis4Stylesheet') }}
				</div>
				<template v-if="isRedirect">
					<a :href="cis4PreviewUrl" target="_blank">
						{{ $p.t('cms/vorschauInEigenemFenster') }}
					</a>
				</template>
				<template v-else>
					<div class="position-relative border" :style="cis4WrapStyle">
						<iframe
							ref="cis4Frame"
							:src="cis4PreviewUrl"
							@load="onCis4Load"
							style="width: 100%; min-height: 500px; border: 0;
								display: block;"
						></iframe>
						<div v-if="cis4Loading"
							class="position-absolute top-0 start-0 w-100 h-100 d-flex
								flex-column align-items-center justify-content-center"
							style="background: rgba(255, 255, 255, 0.85);">
							<div class="spinner-border text-primary" role="status">
								<span class="visually-hidden">{{ $p.t('ui/loading') }}</span>
							</div>
							<div class="mt-2 text-muted small">
								{{ $p.t('cms/vorschauLaedt') }}
							</div>
						</div>
					</div>
				</template>
			</template>
		</div>
	`
};
