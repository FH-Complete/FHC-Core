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
		sprache() {
			return this.$p.user_language.value;
		},
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
		parsedTranslationText() {
			const text = this.translation?.text ?? '';
			if (!text) {
				return '';
			}

			const appRoot = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/\/+$/, '');

			return text.replace(
				/(?:https?:\/\/|\/|\.\.\/)?[^"'<>\s]*?dms\.php(\?[^"'<>\s]*)/gi,
				`${appRoot}/cms/dms.php$1`,
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
	created() {
		this.$p.loadCategory(['global', 'ui']).then(() => {
			this.phrasesLoaded = true;
		});
	},
	methods: {
		getLanguageLabel(language) {
			this.sprache;
			const languagePhrases = {
				German: ['global', 'deutsch'],
				English: ['global', 'englisch'],
				French: ['ui', 'franzoesisch'],
				Spanish: ['ui', 'spanisch'],
			};
			const phrase = languagePhrases[language];

			return phrase
				? this.$capitalize(this.$p.t(phrase[0], phrase[1]))
				: language;
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
					<h4 id="news-item-preview-heading" class="card-title fhc-primary-color mb-0">{{ $capitalize($p.t('ui', 'newsPreview')) }}</h4>
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
						{{ getLanguageLabel(language) }}
					</option>
				</form-input>
				</div>
				<article v-if="translation" class="card border">
					<header class="card-header" :class="translation.isPublished ? 'fhc-primary' : 'bg-secondary bg-opacity-25 text-secondary'">
						<div class="d-flex justify-content-between align-items-start gap-3">
							<h5 class="mb-0">{{ translation.title || $capitalize($p.t('ui', 'untitledNews')) }}</h5>
							<span v-if="!translation.isPublished" class="badge text-bg-secondary">{{ $capitalize($p.t('ui', 'notPublishedYet')) }}</span>
						</div>
						<address v-if="translation.author" class="small mb-0 mt-2">{{ translation.author }}</address>
					</header>
					<div class="card-body">
						<div v-if="parsedTranslationText" class="card-text" v-html="parsedTranslationText"></div>
						<p v-else class="text-muted mb-0">{{ $capitalize($p.t('ui', 'newsContentPreviewHint')) }}</p>
					</div>
				</article>
				<p v-else class="text-muted mb-0">{{ $capitalize($p.t('ui', 'selectLanguageTabToPreview')) }}</p>
			</div>
		</section>
	`,
};
