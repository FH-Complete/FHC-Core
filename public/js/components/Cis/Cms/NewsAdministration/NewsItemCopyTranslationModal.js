import BootstrapModal from '../../../Bootstrap/Modal.js';

export default {
	name: 'NewsItemCopyTranslationModal',
	components: {
		BootstrapModal,
	},
	emits: ['copy', 'hidden'],
	props: {
		sourceLanguages: {
			type: Array,
			default: () => [],
		},
		targetLanguageLabel: {
			type: String,
			default: '',
		},
		targetHasContent: Boolean,
	},
	data() {
		return {
			selectedSourceLanguage: null,
		};
	},
	methods: {
		show() {
			this.selectedSourceLanguage = null;
			this.$refs.modal.show();
		},
		hide() {
			this.$refs.modal.hide();
		},
		selectSourceLanguage(language) {
			if (this.targetHasContent) {
				this.selectedSourceLanguage = language;
				return;
			}

			this.copyTranslation(language);
		},
		copyTranslation(language = this.selectedSourceLanguage) {
			const sourceLanguage = language?.value ?? language;

			if (!sourceLanguage) {
				return;
			}

			this.$emit('copy', sourceLanguage);
			this.hide();
		},
		resetSelection() {
			this.selectedSourceLanguage = null;
			this.$emit('hidden');
		},
	},
	template: /*html*/ `
		<bootstrap-modal
			ref="modal"
			dialog-class="modal-dialog-centered"
			body-class="p-4"
			@hidden-bs-modal="resetSelection"
		>
			<template #title>
				{{ selectedSourceLanguage ? 'Overwrite translation content?' : 'Copy translation content' }}
			</template>
			<template v-if="selectedSourceLanguage">
				<p class="mb-3">
					Copying from <strong>{{ selectedSourceLanguage.label }}</strong> will replace the author, title, and text in <strong>{{ targetLanguageLabel }}</strong>.
				</p>
				<p class="text-muted small">The existing content in the current language will be lost.</p>
				<div class="d-flex justify-content-end gap-2 mt-4">
					<button type="button" class="btn btn-outline-secondary" @click="selectedSourceLanguage = null">
						Choose another language
					</button>
					<button type="button" class="btn btn-primary" @click="copyTranslation()">
						Overwrite and copy
					</button>
				</div>
			</template>
			<template v-else>
				<p class="text-muted">Choose the language whose content should be copied into {{ targetLanguageLabel }}.</p>
				<div v-if="sourceLanguages.length" class="d-grid gap-2">
					<button
						v-for="language in sourceLanguages"
						:key="language.value"
						type="button"
						class="btn btn-outline-primary text-start"
						@click="selectSourceLanguage(language)"
					>
						{{ language.label }}
					</button>
				</div>
				<p v-else class="mb-0 text-muted">Add another language before copying content.</p>
			</template>
		</bootstrap-modal>
	`,
};
