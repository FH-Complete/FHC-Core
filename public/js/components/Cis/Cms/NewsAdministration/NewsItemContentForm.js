import FormInput from '../../../Form/Input.js';

export default {
	name: 'NewsItemContentForm',
	components: {
		FormInput,
	},
	emits: ['update:modelValue', 'tab-action'],
	props: {
		config: {
			type: Object,
			default: () => ({}),
		},
		modelValue: {
			type: Array,
			default: () => [],
		},
	},
	data() {
		const translation = this.modelValue.find(
			(item) => item.language === this.config.language,
		);

		return {
			editor: null,
			dmsFileBrowser: null,
			dmsFileBrowserClosePollId: null,
			dmsFileBrowserMessageHandler: null,
			contentData: {
				language: this.config.language,
				author: '',
				title: '',
				text: '',
				isPublished: false,
				...translation,
			},
		};
	},
	watch: {
		contentData: {
			deep: true,
			handler(contentData) {
				const translations = this.modelValue.map((item) => ({ ...item }));
				const index = translations.findIndex(
					(item) => item.language === this.config.language,
				);
				const translation = {
					...contentData,
					language: this.config.language,
				};

				if (index === -1) {
					translations.push(translation);
				} else {
					translations[index] = translation;
				}

				this.$emit('update:modelValue', translations);
			},
		},
	},
	methods: {
		cleanupDmsFileBrowser(closePopup = false) {
			if (this.dmsFileBrowserClosePollId !== null) {
				window.clearInterval(this.dmsFileBrowserClosePollId);
				this.dmsFileBrowserClosePollId = null;
			}

			if (this.dmsFileBrowserMessageHandler) {
				window.removeEventListener(
					'message',
					this.dmsFileBrowserMessageHandler,
				);
				this.dmsFileBrowserMessageHandler = null;
			}

			if (closePopup && this.dmsFileBrowser && !this.dmsFileBrowser.closed) {
				this.dmsFileBrowser.close();
			}

			this.dmsFileBrowser = null;
		},
		openDmsFileBrowser(callback, meta) {
			this.cleanupDmsFileBrowser(true);

			const appRoot =
				FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/\/+$/, '') + '/';
			const fileType = meta && meta.filetype ? meta.filetype : 'file';
			const fileBrowserUrl =
				appRoot + 'cms/tinymce_dms.php?type=' + encodeURIComponent(fileType);
			const popupName = this.editor
				? 'FHCFileBrowser_' + this.editor.id
				: 'FHCFileBrowser';
			const popup = window.open(
				fileBrowserUrl,
				popupName,
				'width=750,height=550,resizable=yes,scrollbars=yes',
			);

			if (!popup) {
				return;
			}

			this.dmsFileBrowser = popup;

			this.dmsFileBrowserMessageHandler = (event) => {
				const message = event.data;

				if (
					event.origin !== window.location.origin ||
					event.source !== popup ||
					this.dmsFileBrowser !== popup ||
					!message ||
					message.type !== 'fhcomplete:dms:selected' ||
					!Number.isInteger(message.dmsId) ||
					message.dmsId <= 0
				) {
					return;
				}

				try {
					callback(
						appRoot + 'cms/dms.php?id=' + encodeURIComponent(message.dmsId),
					);
				} finally {
					this.cleanupDmsFileBrowser(true);
				}
			};
			window.addEventListener('message', this.dmsFileBrowserMessageHandler);

			this.dmsFileBrowserClosePollId = window.setInterval(() => {
				if (this.dmsFileBrowser === popup && popup.closed) {
					this.cleanupDmsFileBrowser();
				}
			}, 250);
			popup.focus();
		},
		initTinyMCE() {
			const vm = this;
			tinymce.init({
				target: this.$refs.editor.$refs.input, // Important: enables multiple component instances
				height: 500,
				toolbar:
					'styleselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | link unlink image | bullist | pastetext',
				plugins: 'lists link image paste',
				paste_as_text: true,
				statusbar: true,
				resize: true,
				file_picker_types: 'file image',
				file_picker_callback: (callback, _value, meta) => {
					vm.openDmsFileBrowser(callback, meta);
				},

				style_formats: [
					{ title: vm.$capitalize(vm.$p.t('ui', 'blocks')), block: 'div' },
					{ title: vm.$capitalize(vm.$p.t('ui', 'paragraph')), block: 'p' },
					{ title: vm.$capitalize(vm.$p.t('ui', 'heading1')), block: 'h1' },
					{ title: vm.$capitalize(vm.$p.t('ui', 'heading2')), block: 'h2' },
					{ title: vm.$capitalize(vm.$p.t('ui', 'heading3')), block: 'h3' },
					{ title: vm.$capitalize(vm.$p.t('ui', 'heading4')), block: 'h4' },
					{ title: vm.$capitalize(vm.$p.t('ui', 'heading5')), block: 'h5' },
					{ title: vm.$capitalize(vm.$p.t('ui', 'heading6')), block: 'h6' },
				],
				setup: (editor) => {
					vm.editor = editor;

					editor.on('input change undo redo', () => {
						vm.contentData.text = editor.getContent();
					});
				},
			});
		},
	},
	async mounted() {
		await this.$p.loadCategory(['global', 'ui', 'notiz']);

		setTimeout(() => {
			if (this.$refs.editor?.$refs.input) {
				this.initTinyMCE();
			}
		}, 100);
	},
	beforeUnmount() {
		this.cleanupDmsFileBrowser(true);

		if (this.editor) {
			this.editor.destroy();
		}
	},
	template: /*html*/ `
	<div class="position-relative overflow-x-hidden">
	<button
		v-if="config.language !== 'German'"
		type="button"
		class="btn btn-sm btn-outline-danger position-absolute top-0 end-0"
		:title="$capitalize($p.t('ui', 'removeLanguage'))"
		:aria-label="$capitalize($p.t('ui', 'removeLanguage'))"
		@click="$emit('tab-action', { action: 'remove-language', language: config.language })"
	>
		<i class="fa-solid fa-trash-can" aria-hidden="true"></i>
	</button>
    <div class="d-flex flex-column flex-md-row align-items-md-end gap-3">
      <form-input
        type="text"
		:label="$capitalize($p.t('notiz', 'verfasser'))"
        name="author"
        v-model="contentData.author"
        >
      </form-input>
      <form-input
        type="text"
		:label="$capitalize($p.t('global', 'betreff'))"
        name="title"
        v-model="contentData.title"
        >
      </form-input>
      <form-input
        type="checkbox"
		:label="$capitalize($p.t('ui', 'publish'))"
        name="isPublished"
        v-model="contentData.isPublished"
        >
      </form-input>
    </div>
    <form-input
      ref="editor"
		:label="$capitalize($p.t('global', 'text') + ' *')"
      type="textarea"
      name="text"
      v-model="contentData.text"
      rows="5"
      cols="75"
      style="min-height: 500px"
      >
    </form-input>

</div>
    `,
};
