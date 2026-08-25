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
			<div v-if="config?.languages?.length" class="d-flex justify-content-center gap-2">
				<button
					v-for="language in config.languages"
					:key="language.value"
					type="button"
					class="btn btn-outline-primary text-start"
					@click="$emit('tab-action', { action: 'add-language', language: language.value })"
				>
					{{ language.label }}
				</button>
			</div>
			<p v-else class="mb-0 text-muted">{{ $capitalize($p.t('ui', 'allLanguagesAdded')) }}</p>
		</div>
	`,
};
