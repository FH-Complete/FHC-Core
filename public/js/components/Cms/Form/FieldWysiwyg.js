export default {
	name: 'FieldWysiwyg',
	props: {
		modelValue: { type: String, default: '' },
		label: String,
		required: Boolean,
		disabled: Boolean
	},
	emits: ['update:modelValue'],
	data() {
		return {
			editor: null,
			editorId: 'cms-wysiwyg-' + Math.random().toString(36).substring(2, 11)
		};
	},
	methods: {
		getContent() {
			return this.editor ? this.editor.getContent() : this.modelValue;
		}
	},
	watch: {
		modelValue(newVal) {
			if (this.editor && this.editor.getContent() !== newVal) {
				this.editor.setContent(newVal || '');
			}
		}
	},
	mounted() {
		const vm = this;
		tinymce.init({
			target: this.$refs.editorArea,
			height: 500,
			language: 'de',
			// valid_elements: '*[*]' permits every tag and attribute. Existing content
			// contains audio, video and source tags. A stricter setting deletes them silently.
			valid_elements: '*[*]',
			forced_root_block: '',
			style_formats: [
				{ title: 'Contenttabelle', selector: 'table', classes: 'cmstable' },
				{ title: 'Hauptcontent', block: 'div', classes: 'cmscontent' },
				{ title: 'Menuebox', block: 'div', classes: 'menubox' },
				{ title: 'Teambox', block: 'div', classes: 'teambox' },
				{ title: 'Schatteneffekt Grafiken', selector: 'img', classes: 'screenshot_boxshadow' },
				{ title: 'Tablesorter', selector: 'table',
				  classes: 'tablesorter tablesorter_col_0 tablesorter_sort_0' }
			],
			plugins: 'code lists link image table fullscreen paste searchreplace charmap directionality visualblocks anchor hr',
			toolbar: [
				'code | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect fontsizeselect',
				'pastetext | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image | forecolor backcolor',
				'table | hr removeformat visualblocks | subscript superscript | charmap | ltr rtl | fullscreen'
			],
			file_picker_callback: (callback, value, meta) => {
				if (vm._dmsListener) {
					window.removeEventListener('message', vm._dmsListener);
				}

				const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + 'cms/tinymce_dms.php';
				window.open(url, 'DMS', 'width=1400,height=850,scrollbars=1,resizable=1');

				const onMessage = (event) => {
					if (event.origin !== window.location.origin) return;
					if (!event.data || event.data.typ !== 'fhc-dms-selection') return;
					// The relative form dms.php?id=N is mandatory. The legacy view runs under /cms/
					// and resolves it directly. The CIS4 view replaces it in CmsLib::getContent
					// with APP_ROOT . 'cms/dms.php'. An absolute URL breaks both paths.
					callback(event.data.url);
					window.removeEventListener('message', onMessage);
					vm._dmsListener = null;
				};
				vm._dmsListener = onMessage;
				window.addEventListener('message', onMessage);
			},
			setup: (editor) => {
				vm.editor = editor;
				editor.on('init', () => {
					editor.setContent(vm.modelValue || '');
					if (vm.disabled) editor.mode.set('readonly');
				});
				editor.on('change input', () => {
					vm.$emit('update:modelValue', editor.getContent());
				});
			}
		});
	},
	beforeUnmount() {
		if (this._dmsListener) {
			window.removeEventListener('message', this._dmsListener);
			this._dmsListener = null;
		}
		if (this.editor && tinymce.get(this.editor.id)) {
			tinymce.get(this.editor.id).remove();
			this.editor = null;
		}
	},
	template: `
		<div class="mb-3">
			<label class="form-label">
				{{ label }}<span v-if="required" class="text-danger"> *</span>
			</label>
			<textarea ref="editorArea" :id="editorId"></textarea>
		</div>
	`
};
