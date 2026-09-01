import { CONTENTCOMPONENT_CLASS, CONTENTCOMPONENT_ATTR } from '../../Contentcomponents/catalog.js';
import { createContentcomponentDialog } from './ContentcomponentDialog.js';

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
		},

		// Opens the legacy DMS browser and resolves with the relative URL it posts back.
		// Used for images and links, where the editor needs the file preview of that
		// browser. The contentcomponent dialog picks documents from a list instead, without
		// leaving the dialog. The bridge in cms/tinymce_dms.php sends { typ, url } to
		// window.opener and closes itself.
		openDmsBrowser() {
			const vm = this;

			return new Promise(resolve => {
				if (vm._dmsListener)
					window.removeEventListener('message', vm._dmsListener);

				const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + 'cms/tinymce_dms.php';
				// The window name selects the bridge path in cms/tinymce_dms.php and survives
				// its internal navigation. The legacy admin opens the same page as 'DMS'.
				window.open(url, 'DMS_CIS4', 'width=1400,height=850,scrollbars=1,resizable=1');

				const onMessage = (event) => {
					if (event.origin !== window.location.origin) return;
					if (!event.data || event.data.typ !== 'fhc-dms-selection') return;

					window.removeEventListener('message', onMessage);
					vm._dmsListener = null;
					resolve(event.data.url);
				};

				vm._dmsListener = onMessage;
				window.addEventListener('message', onMessage);
			});
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

		// The dialogs live in their own module. This file stays with the editor itself.
		this.contentcomponentDialog = createContentcomponentDialog({
			api: this.$api,
			onChanged: () => vm.$emit('update:modelValue', vm.editor.getContent())
		});

		tinymce.init({
			target: this.$refs.editorArea,
			height: 500,
			language: 'de',
			// valid_elements: '*[*]' permits every tag and attribute. Existing content
			// contains audio, video and source tags. A stricter setting deletes them silently.
			valid_elements: '*[*]',
			// A contentcomponent marker is one atomic block. The editor must not type into it.
			noneditable_noneditable_class: CONTENTCOMPONENT_CLASS,
			// The marker renders nothing here, so the editor has to show that it is there.
			content_style: '.' + CONTENTCOMPONENT_CLASS + '{'
				+ 'display:block;margin:.5rem 0;padding:.5rem;min-height:2.5rem;'
				+ 'border:1px dashed #7a7a7a;background:#f2f2f2;cursor:pointer;}'
				+ '.' + CONTENTCOMPONENT_CLASS + '::before{'
				+ 'content:"Contentcomponent: " attr(' + CONTENTCOMPONENT_ATTR + ');'
				+ 'font:12px monospace;color:#555;}',
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
			plugins: 'code lists link image table fullscreen paste searchreplace charmap directionality visualblocks anchor hr noneditable',
			toolbar: [
				'code | contentcomponent | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect fontsizeselect',
				'pastetext | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image | forecolor backcolor',
				'table | hr removeformat visualblocks | subscript superscript | charmap | ltr rtl | fullscreen'
			],
			// The relative form dms.php?id=N is mandatory. The legacy view runs under /cms/
			// and resolves it directly. The CIS4 view replaces it in CmsLib::getContent
			// with APP_ROOT . 'cms/dms.php'. An absolute URL breaks both paths.
			file_picker_callback: (callback, value, meta) => {
				vm.openDmsBrowser().then(callback);
			},
			setup: (editor) => {
				vm.editor = editor;

				editor.ui.registry.addButton('contentcomponent', {
					text: 'Contentcomponent',
					tooltip: 'Contentcomponent einfügen',
					onAction: () => vm.contentcomponentDialog.openPicker(editor)
				});

				// A double click on a marker reopens its property dialog.
				editor.on('dblclick', (event) => {
					const node = editor.dom.getParent(event.target, '.' + CONTENTCOMPONENT_CLASS);

					if (node)
						vm.contentcomponentDialog.openMarker(editor, node);
				});

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
