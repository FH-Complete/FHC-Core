import Phrasen from '../../plugin/Phrasen.js';

export default {
	data: () => ({
		modal: null
	}),
	props: {
		backdrop: {
			type: [Boolean,String],
			default: true,
			validator(value) {
				return ['static', true, false].includes(value);
			}
		},
		focus: {
			type: Boolean,
			default: true
		},
		keyboard: {
			type: Boolean,
			default: true
		},
		noCloseBtn: Boolean,
		dialogClass: [String,Array,Object],
		bodyClass: {
			type: [String,Array,Object],
			default: 'px-4 py-5'
		}
	},
	emits: [
		"hideBsModal",
		"hiddenBsModal",
		"hidePreventedBsModal",
		"showBsModal",
		"shownBsModal"
	],
	methods: {
		dispose() {
			return this.modal.dispose();
		},
		handleUpdate() {
			return this.modal.handleUpdate();
		},
		hide() {
			return this.modal.hide();
		},
		show(relatedTarget) {
			return this.modal.show(relatedTarget);
		},
		toggle() {
			return this.modal.toggle();
		}
	},
	mounted() {
		if (this.$refs.modal)
			this.modal = new bootstrap.Modal(this.$refs.modal, {
				backdrop: this.backdrop,
				focus: this.focus,
				keyboard: this.keyboard
			});
	},
	popup(body, options, title, footer) {
		const BsModal = this,
			slots = {};
		if (body !== undefined)
			slots.default = () => body;
		if (title !== undefined)
			slots.title = () => title;
		if (footer !== undefined)
			slots.footer = () => footer;

		// little hack to check whether primevue is included in the app or not
		let includedPrimevue = false;
		if(typeof primevue !== 'undefined'){
			includedPrimevue = true;
		}

		return new Promise((resolve,reject) => {
			const instance = Vue.createApp({
				setup() {
					return () => Vue.h(BsModal, {...{
						class: 'fade'
					},...options, ...{
						ref: 'modal',
						'onHidden.bs.modal': instance.unmount
					}}, slots);
				},
				mounted() {
					this.$refs.modal.show();
					
				},
				beforeUnmount() {
					if (this.$refs.modal)
						this.$refs.modal.result !== false ? resolve(this.$refs.modal.result) : reject();
				},
				unmounted() {
					wrapper.parentElement.removeChild(wrapper);
				}
			});
			const wrapper = document.createElement("div");
			
			// if(primevue) --> won't work because primevue is not defined in this scope and promise would be rejected
			if (includedPrimevue){
				instance.use(primevue.config.default, {zIndex: {overlay: 9999}})
			}
				 
			instance.use(Phrasen); // TODO(chris): find a more dynamic way
			instance.mount(wrapper);
			document.body.appendChild(wrapper);
		});
	},
	template: `<div ref="modal" class="bootstrap-modal modal" tabindex="-1" @[\`hide.bs.modal\`]="$emit('hideBsModal')" @[\`hidden.bs.modal\`]="$emit('hiddenBsModal')" @[\`hidePrevented.bs.modal\`]="$emit('hidePreventedBsModal')" @[\`show.bs.modal\`]="$emit('showBsModal')" >
		<div class="modal-dialog" :class="dialogClass">
			<div class="modal-content">
				<div v-if="$slots.title" class="modal-header">
					<h5 class="modal-title"><slot name="title"/></h5>
					<button v-if="!noCloseBtn" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" :class="bodyClass">
					<slot></slot>
				</div>
				<div v-if="$slots.footer" class="modal-footer">
					<slot name="footer"/>
				</div>
			</div>
		</div>
	</div>`
}
