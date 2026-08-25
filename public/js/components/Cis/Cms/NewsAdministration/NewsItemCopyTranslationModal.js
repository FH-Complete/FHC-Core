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
		getCopyingTranslationText() {
			return this.$capitalize(
				this.$p.t('ui', 'copyingTranslation', {
					source: this.selectedSourceLanguage?.label ?? '',
					target: this.targetLanguageLabel,
				}),
			);
		},
		getChooseLanguageText() {
			return this.$capitalize(
				this.$p.t('ui', 'chooseLanguageToCopyContent', {
					target: this.targetLanguageLabel,
				}),
			);
		},
	},
	created() {
		this.$p.loadCategory('ui').then(() => {
			this.phrasesLoaded = true;
		});
	},
	template: /*html*/ `
		<bootstrap-modal
			ref="modal"
			dialog-class="modal-dialog-centered"
			body-class="p-4"
			@hidden-bs-modal="resetSelection"
		>
			<template #title>
				{{ $capitalize(selectedSourceLanguage ? $p.t('ui', 'overwriteTranslationContent') : $p.t('ui', 'copyTranslationContent')) }}
			</template>
			<template v-if="selectedSourceLanguage">
				<p class="mb-3">
					{{ getCopyingTranslationText() }}
				</p>
				<p class="text-muted small">{{ $capitalize($p.t('ui', 'existingContentWillBeLost')) }}</p>
				<div class="d-flex justify-content-end gap-2 mt-4">
					<button type="button" class="btn btn-outline-secondary" @click="selectedSourceLanguage = null">
						{{ $capitalize($p.t('ui', 'chooseAnotherLanguage')) }}
					</button>
					<button type="button" class="btn btn-primary" @click="copyTranslation()">
						{{ $capitalize($p.t('ui', 'overwriteAndCopy')) }}
					</button>
				</div>
			</template>
			<template v-else>
				<p class="text-muted">{{ getChooseLanguageText() }}</p>
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
				<p v-else class="mb-0 text-muted">{{ $capitalize($p.t('ui', 'addLanguageBeforeCopying')) }}</p>
			</template>
		</bootstrap-modal>
	`,
};
