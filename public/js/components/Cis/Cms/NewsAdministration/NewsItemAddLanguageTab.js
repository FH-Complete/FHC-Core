export default {
	name: 'NewsItemAddLanguageTab',
	emits: ['tab-action'],
	props: {
		config: {
			type: Object,
			default: () => ({}),
		},
	},
	template: /*html*/ `
		<div class="py-4 text-center text-muted">
			<button
				v-if="config.hasAvailableLanguages"
				type="button"
				class="btn btn-link text-decoration-none"
				aria-label="Add translation language"
				@click="$emit('tab-action')"
			>
				<i class="fa-solid fa-plus-circle fs-4 mb-2" aria-hidden="true"></i>
				<span class="d-block">Select a language to add a translation.</span>
			</button>
			<p v-else class="mb-0">All available languages have been added.</p>
		</div>
	`,
};
