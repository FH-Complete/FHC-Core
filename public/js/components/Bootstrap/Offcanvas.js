export default {
	name: 'BootstrapOffcanvas',
	data: () => ({
		offcanvas: null
	}),
	props: {
		backdrop: {
			type: [Boolean, String],
			default: true,
			validator(value) {
				return ['static', true, false].includes(value);
			}
		},
		keyboard: {
			type: Boolean,
			default: true
		},
		scroll: {
			type: Boolean,
			default: false
		},
		placement: {
			type: String,
			default: 'start', // start | end | top | bottom
			validator(value) {
				return ['start', 'end', 'top', 'bottom'].includes(value);
			}
		},
		noCloseBtn: Boolean,
		headerClass: {
			type: [String, Array, Object],
			default: ''
		},
		bodyClass: {
			type: [String, Array, Object],
			default: 'p-4'
		},
		footerClass: {
			type: [String, Array, Object],
			default: ''
		},
		dialogClass: [String, Array, Object]
	},
	emits: [
		"hideBsOffcanvas",
		"hiddenBsOffcanvas",
		"hidePreventedBsOffcanvas",
		"showBsOffcanvas",
		"shownBsOffcanvas"
	],
	methods: {
		dispose() {
			return this.offcanvas?.dispose();
		},
		hide() {
			return this.offcanvas?.hide();
		},
		show(relatedTarget) {
			return this.offcanvas?.show(relatedTarget);
		},
		toggle() {
			return this.offcanvas?.toggle();
		},
		popup(body, options, title, footer) {
			const BsOffcanvas = this,
				slots = {};

			if (body !== undefined)
				slots.default = () => body;
			if (title !== undefined)
				slots.title = () => title;
			if (footer !== undefined)
				slots.footer = () => footer;

			let includedPrimevue = false;
			if (typeof primevue !== 'undefined')
				includedPrimevue = true;

			return new Promise((resolve, reject) => {
				const instance = Vue.createApp({
					name: 'OffcanvasTmpApp',
					setup() {
						return () =>
							Vue.h(BsOffcanvas, {
								class: 'offcanvas-wrapper',
								ref: 'offcanvas',
								...options
							}, slots);
					},
					mounted() {
						this.$refs.offcanvas.show();
					},
					beforeUnmount() {
						if (this.$refs.offcanvas)
							this.$refs.offcanvas.result !== false ? resolve(this.$refs.offcanvas.result) : reject();
					},
					unmounted() {
						wrapper.parentElement.removeChild(wrapper);
					}
				});
				const wrapper = document.createElement('div');

				if (includedPrimevue) {
					instance.use(primevue.config.default, { zIndex: { overlay: 9999 } });
				}

				import('../../plugins/Phrasen.js').then((Phrasen) => {
					instance.use(Phrasen.default);
					instance.mount(wrapper);
					document.body.appendChild(wrapper);
				});
			});
		}
	},
	mounted() {
		if (this.$refs.offcanvas) {
			this.offcanvas = new bootstrap.Offcanvas(this.$refs.offcanvas, {
				backdrop: this.backdrop,
				keyboard: this.keyboard,
				scroll: this.scroll
			});
		}
	},
	template: `
		<div ref="offcanvas"
			class="bootstrap-offcanvas offcanvas"
			:class="['offcanvas-' + placement, dialogClass]"
			tabindex="-1"
			@[\`hide.bs.offcanvas\`]="$emit('hideBsOffcanvas')"
			@[\`hidden.bs.offcanvas\`]="$emit('hiddenBsOffcanvas')"
			@[\`hidePrevented.bs.offcanvas\`]="$emit('hidePreventedBsOffcanvas')"
			@[\`show.bs.offcanvas\`]="$emit('showBsOffcanvas')"
			@[\`shown.bs.offcanvas\`]="$emit('shownBsOffcanvas')"
		>
			<div class="offcanvas-header" :class="headerClass" v-if="$slots.title">
				<h5 class="offcanvas-title">
					<slot name="title"></slot>
				</h5>
				<button v-if="!noCloseBtn" type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
			</div>

			<div class="offcanvas-body" :class="bodyClass">
				<slot></slot>
			</div>

			<div v-if="$slots.footer" class="offcanvas-footer" :class="footerClass">
				<slot name="footer"></slot>
			</div>
		</div>
	`
}