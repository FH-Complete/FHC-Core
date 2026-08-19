import FormInput from '../../../Form/Input.js';

export default {
	name: 'NewsItemPreview',
	components: {
		FormInput,
	},
	props: {
		formData: {
			type: Object,
			default: null,
		},
		activeLanguage: {
			type: String,
			default: '',
		},
	},
	data() {
		return {
			selectedLanguage: '',
		};
	},
	computed: {
		availableLanguages() {
			return (this.formData?.translations ?? [])
				.map((translation) => translation.language)
				.filter(Boolean);
		},
		displayedLanguage() {
			return this.selectedLanguage || this.activeLanguage;
		},
		translation() {
			return this.formData?.translations?.find(
				(item) => item.language === this.displayedLanguage,
			);
		},
	},
	watch: {
		activeLanguage: {
			immediate: true,
			handler(language) {
				if (this.availableLanguages.includes(language)) {
					this.selectedLanguage = language;
				}
			},
		},
		availableLanguages(languages) {
			if (languages.includes(this.selectedLanguage)) {
				return;
			}

			this.selectedLanguage = languages.includes(this.activeLanguage)
				? this.activeLanguage
				: (languages[0] ?? '');
		},
	},
	template: /*html*/ `
		<section
			id="news-item-preview"
			class="card bg-white shadow-sm border-0 mb-4 position-sticky"
			style="top: 1rem"
			aria-labelledby="news-item-preview-heading"
		>
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center gap-3 mb-3">
					<h4 id="news-item-preview-heading" class="card-title fhc-primary-color mb-0">News preview</h4>
					<form-input
					v-if="availableLanguages.length > 1"
					v-model="selectedLanguage"
					type="select"
					name="previewLanguage"
					class="m-0"
				>
					<option
						v-for="language in availableLanguages"
						:key="language"
						:value="language"
					>
						{{ language }}
					</option>
				</form-input>
				</div>
				<article v-if="translation" class="card border">
					<header class="card-header" :class="translation.isPublished ? 'fhc-primary' : 'bg-secondary bg-opacity-25 text-secondary'">
						<div class="d-flex justify-content-between align-items-start gap-3">
							<h5 class="mb-0">{{ translation.title || 'Untitled news' }}</h5>
							<span v-if="!translation.isPublished" class="badge text-bg-secondary">Not published yet</span>
						</div>
						<address v-if="translation.author" class="small mb-0 mt-2">{{ translation.author }}</address>
					</header>
					<div class="card-body">
						<div v-if="translation.text" class="card-text" v-html="translation.text"></div>
						<p v-else class="text-muted mb-0">Enter text to see the news content preview.</p>
					</div>
				</article>
				<p v-else class="text-muted mb-0">Select a language tab to preview its content.</p>
			</div>
		</section>
	`,
};
