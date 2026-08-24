export default {
	name: 'NewsItemAddLanguageTab',
	emits: ['tab-action'],
	props: {
		config: {
			type: Object,
			default: () => ({}),
		},
	},
	created() {
		this.$p.loadCategory('ui').then(() => {
			this.phrasesLoaded = true;
		});
	},
	template: /*html*/ `
		<div class="py-4 text-center text-muted">
			<button
				v-if="config.hasAvailableLanguages"
				type="button"
				class="btn btn-link text-decoration-none"
				:aria-label="$p.t('ui', 'addTranslationLanguage')"
				@click="$emit('tab-action')"
			>
				<i class="fa-solid fa-plus-circle fs-4 mb-2" aria-hidden="true"></i>
				<span class="d-block">{{ $p.t('ui', 'selectLanguageForTranslation') }}</span>
			</button>
			<p v-else class="mb-0">{{ $p.t('ui', 'allLanguagesAdded') }}</p>
		</div>
	`,
};
