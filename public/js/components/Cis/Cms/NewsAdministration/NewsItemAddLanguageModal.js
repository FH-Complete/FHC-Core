import BootstrapModal from '../../../Bootstrap/Modal.js';

export default {
	name: 'NewsItemAddLanguageModal',
	components: {
		BootstrapModal,
	},
	emits: ['add-language', 'hidden'],
	props: {
		languages: {
			type: Array,
			default: () => [],
		},
	},
	methods: {
		show() {
			this.$refs.modal.show();
		},
		hide() {
			this.$refs.modal.hide();
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
			@hidden-bs-modal="$emit('hidden')"
		>
			<template #title>{{ $p.t('ui', 'addTranslationLanguage') }}</template>
			<div v-if="languages.length" class="d-grid gap-2">
				<button
					v-for="language in languages"
					:key="language.value"
					type="button"
					class="btn btn-outline-primary text-start"
					@click="$emit('add-language', language.value)"
				>
					{{ language.label }}
				</button>
			</div>
			<p v-else class="mb-0 text-muted">{{ $p.t('ui', 'allLanguagesAdded') }}</p>
		</bootstrap-modal>
	`,
};
