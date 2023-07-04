import BsModal from './Bootstrap/Modal.js';

export default {
	components: {
		BsModal
	},
	props: {
		timeout: {
			type: Number,
			default: 300
		}
	},
	data() {
		return {
			t: null,
			state: 0
		}
	},
	methods: {
		show() {
			switch (this.state) {
				case 0:
					if (this.timeout) {
						this.state = 1;
						this.t = window.setTimeout(() => this.$refs.modal.show(), this.timeout);
						return;
					} else
						return this.$refs.modal.show();
				case 4:
					return window.setTimeout(() => this.show(), 1);
			}
		},
		hide() {
			switch (this.state) {
				case 1:
					return window.clearTimeout(this.t);
				case 2:
					return window.setTimeout(() => this.hide(), 1);
				case 3:
					this.$refs.modal.hide();
			}
		}
	},
	mounted() {
		this.$refs.modal.$refs.modal.addEventListener('show.bs.modal', () => {
			this.state = 2;
		});
		this.$refs.modal.$refs.modal.addEventListener('shown.bs.modal', () => {
			this.state = 3;
		});
		this.$refs.modal.$refs.modal.addEventListener('hide.bs.modal', () => {
			this.state = 4;
		});
		this.$refs.modal.$refs.modal.addEventListener('hidden.bs.modal', () => {
			this.state = 0;
		});
	},
	template: `
	<bs-modal ref="modal" class="fade text-center" dialog-class="modal-dialog-centered" backdrop="static" :keyboard="false">
		Loading...
	</bs-modal>`
}