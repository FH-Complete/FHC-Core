import CoreForm from '../../../Form/Form.js';
import FormInput from '../../../Form/Input.js';
import FhcTabs from '../../../Tabs.js';
import NewsItemCopyTranslationModal from './NewsItemCopyTranslationModal.js';
import ApiStudiengang from '../../../../api/factory/studiengang.js';
import ApiNewsAdministration from '../../../../api/factory/newsAdministration.js';

const BASE_COMPONENT_URL =
	FHC_JS_DATA_STORAGE_OBJECT.app_root +
	'public/js/components/Cis/Cms/NewsAdministration/';

export default {
	name: 'NewsItemForm',
	components: {
		CoreForm,
		FormInput,
		FhcTabs,
		NewsItemCopyTranslationModal,
	},
	emits: [
		'created',
		'update',
		'cancel',
		'translation-copied',
		'preview-change',
		'toggle-preview',
	],
	props: {
		newsItem: {
			type: Object,
			default: null,
		},
		isPreviewShown: Boolean,
	},
	data() {
		return {
			degreePrograms: [],
			filteredDegreePrograms: [],
			isSaving: false,
			activeContentFormKey: 'germanContentForm',
			contentFormRenderKey: 0,
			formData: {
				visibleFrom: null,
				visibleTo: null,
				degreeProgram: null,
				semester: null,
				translations: [],
			},
			contentFormItems: {},
		};
	},
	watch: {
		'$p.user_language.value': function () {
			Object.values(this.contentFormItems).forEach((item) => {
				if (item.config?.language) {
					item.title = this.getContentFormTitle(item.config.language);
				}
			});
		},
		formData: {
			deep: true,
			immediate: true,
			handler() {
				this.emitPreviewChange();
			},
		},
		activeContentLanguage() {
			this.emitPreviewChange();
		},
	},
	computed: {
		sprache: function () {
			return this.$p.user_language.value;
		},
		availableLanguages() {
			this.sprache;

			return FHC_JS_DATA_STORAGE_OBJECT?.server_languages?.map(
				(languageObject) => ({
					label: this.getLanguageLabel(languageObject.sprache),
					value: languageObject.sprache,
				}),
			);
		},
		semesters() {
			this.sprache;
			const semesterLabel = this.$capitalize(
				this.$p.t('lehre', 'semester'),
			);

			return [
				{
					label: this.$capitalize(this.$p.t('ui', 'all_semester')),
					value: null,
				},
				...Array.from({ length: 8 }, (_, index) => ({
					label: `${index + 1}. ${semesterLabel}`,
					value: String(index + 1),
				})),
			];
		},
		dropdownParsedDegreePrograms() {
			return this.degreePrograms
				.map((degreeProgram) => {
					let labelFragment = degreeProgram.typ + degreeProgram.kurzbz;
					labelFragment = labelFragment.trim().toUpperCase();

					return {
						label: `${labelFragment} (${degreeProgram.bezeichnung})`,
						value: degreeProgram.studiengang_kz,
					};
				})
				.sort((a, b) => a.label.localeCompare(b.label));
		},
		visibleContentLanguages() {
			return Object.values(this.contentFormItems)
				.map((item) => item.config?.language)
				.filter(Boolean);
		},
		languagesToAdd() {
			return this.availableLanguages.filter(
				(language) => !this.visibleContentLanguages.includes(language.value),
			);
		},
		activeContentLanguage() {
			return this.contentFormItems[this.activeContentFormKey]?.config?.language;
		},
		copySourceLanguages() {
			return this.visibleContentLanguages
				.filter((language) => language !== this.activeContentLanguage)
				.map((language) => ({
					label: this.getLanguageLabel(language),
					value: language,
				}));
		},
		activeTranslationHasContent() {
			return this.hasTranslationContent(
				this.getTranslation(this.activeContentLanguage),
			);
		},
	},
	methods: {
		emitPreviewChange() {
			this.$emit('preview-change', {
				formData: this.formData,
				activeLanguage: this.activeContentLanguage,
			});
		},
		getContentFormKey(language) {
			return `${language.toLowerCase()}ContentForm`;
		},
		createContentFormItem(language) {
			const key = this.getContentFormKey(language);

			return {
				title: this.getContentFormTitle(language),
				component: BASE_COMPONENT_URL + 'NewsItemContentForm.js?124',
				config: {
					language,
					type: 'news',
				},
				key,
			};
		},
		createAddLanguageTab() {
			return {
				title: '+',
				component: BASE_COMPONENT_URL + 'NewsItemAddLanguageTab.js',
				config: { languages: [] },
				key: 'addLanguage',
			};
		},
		showCopyTranslationModal() {
			this.$refs.copyTranslationModal.show();
		},
		getLanguageLabel(language) {
			const languagePhrases = {
				German: ['global', 'deutsch'],
				English: ['global', 'englisch'],
				French: ['ui', 'franzoesisch'],
				Spanish: ['ui', 'spanisch'],
			};
			const phrase = languagePhrases[language];

			return phrase ? this.$capitalize(this.$p.t(phrase[0], phrase[1])) : language;
		},
		getContentFormTitle(language) {
			return this.$capitalize(
				this.$p.t('ui', 'contentFormTitle', {
					language: this.getLanguageLabel(language),
				}),
			);
		},
		getTranslation(language) {
			return this.formData.translations.find(
				(translation) => translation.language === language,
			);
		},
		hasTranslationContent(translation) {
			return ['author', 'title', 'text'].some(
				(field) => String(translation?.[field] ?? '').trim() !== '',
			);
		},
		copyTranslation(sourceLanguage) {
			const sourceTranslation = this.getTranslation(sourceLanguage);
			const targetTranslation = this.getTranslation(this.activeContentLanguage);

			if (!sourceTranslation || !targetTranslation) {
				return;
			}

			Object.assign(targetTranslation, {
				author: sourceTranslation.author,
				title: sourceTranslation.title,
				text: sourceTranslation.text,
			});

			this.contentFormRenderKey += 1;
			this.$emit('translation-copied', {
				from: sourceLanguage,
				to: this.activeContentLanguage,
			});
		},
		handleContentTabChange(key) {
			this.activeContentFormKey = key;
		},
		handleContentTabAction({ key, payload }) {
			if (payload?.action === 'add-language') {
				this.addLanguage(payload.language);
			} else if (payload?.action === 'remove-language') {
				this.removeLanguage(payload.language);
			}
		},
		removeLanguage(language) {
			const key = this.getContentFormKey(language);

			if (!this.contentFormItems[key]) {
				return;
			}

			const contentFormItems = { ...this.contentFormItems };
			const addLanguageTab = contentFormItems.addLanguage;
			delete contentFormItems[key];
			delete contentFormItems.addLanguage;

			const remainingLanguages = Object.values(contentFormItems)
				.map((item) => item.config?.language)
				.filter(Boolean);
			contentFormItems.addLanguage = {
				...addLanguageTab,
				config: {
					hasAvailableLanguages: this.availableLanguages.some(
						(availableLanguage) =>
							!remainingLanguages.includes(availableLanguage.value),
					),
				},
			};

			this.contentFormItems = contentFormItems;
			this.formData.translations = this.formData.translations.filter(
				(translation) => translation.language !== language,
			);

			const nextContentFormKey = Object.keys(contentFormItems).find(
				(itemKey) => itemKey !== 'addLanguage',
			);
			this.activeContentFormKey = nextContentFormKey ?? 'addLanguage';

			if (nextContentFormKey) {
				this.$nextTick(() => this.$refs.tabs.change(nextContentFormKey));
			}
		},
		addLanguage(language) {
			const key = this.getContentFormKey(language);

			if (this.contentFormItems[key]) {
				return;
			}

			const addLanguageTab = this.contentFormItems.addLanguage;
			const contentFormItems = { ...this.contentFormItems };
			delete contentFormItems.addLanguage;
			contentFormItems[key] = this.createContentFormItem(language);
			contentFormItems.addLanguage = {
				...addLanguageTab,
				config: {
					hasAvailableLanguages: this.availableLanguages.some(
						(availableLanguage) =>
							availableLanguage.value !== language &&
							!this.visibleContentLanguages.includes(availableLanguage.value),
					),
				},
			};
			this.contentFormItems = contentFormItems;

			if (
				!this.formData.translations.some((item) => item.language === language)
			) {
				this.formData.translations.push({
					language,
					author: '',
					title: '',
					text: '',
					isPublished: false,
				});
			}

			this.activeContentFormKey = key;
			this.$nextTick(() => this.$refs.tabs.change(key));
		},
		restoreActiveContentTab() {
			this.$refs.tabs.change(this.activeContentFormKey);
		},
		fillFormData(newsItem) {
			this.formData.visibleFrom =
				newsItem?.visibleFrom ?? newsItem?.dateTime?.slice(0, 10) ?? null;
			this.formData.visibleTo = newsItem?.visibleTo ?? null;
			const degreeProgramShortCode =
				newsItem?.degreeProgramShortCode ??
				newsItem?.degreeProgram?.value ??
				null;
			this.formData.degreeProgram =
				this.dropdownParsedDegreePrograms.find(
					(degreeProgram) =>
						String(degreeProgram.value) === String(degreeProgramShortCode),
				) ??
				(degreeProgramShortCode === null
					? null
					: {
							label: String(degreeProgramShortCode),
							value: degreeProgramShortCode,
						});
			this.formData.semester = newsItem?.semester ?? null;

			const translations = newsItem?.translations ?? [
				{
					language: newsItem?.language ?? 'German',
					author: newsItem?.author ?? '',
					title: newsItem?.title ?? '',
					text: newsItem?.content ?? '',
					isPublished: Boolean(newsItem?.isPublished),
				},
			];

			this.formData.translations = translations.map((sourceTranslation) => ({
				language: sourceTranslation.language,
				author: sourceTranslation.author ?? '',
				title: sourceTranslation.title ?? '',
				text: sourceTranslation.text ?? '',
				isPublished: Boolean(sourceTranslation.isPublished),
			}));

			const contentFormItems = {};

			this.formData.translations.forEach((translation) => {
				if (translation.language) {
					contentFormItems[this.getContentFormKey(translation.language)] =
						this.createContentFormItem(translation.language);
				}
			});

			contentFormItems.addLanguage = this.createAddLanguageTab();
			this.contentFormItems = contentFormItems;
			this.activeContentFormKey =
				Object.keys(contentFormItems).find((key) => key !== 'addLanguage') ??
				'addLanguage';
		},
		filterDegreePrograms(event) {
			const query = event.query.toLowerCase();

			return (this.filteredDegreePrograms =
				this.dropdownParsedDegreePrograms.filter((unit) => {
					return unit.label.toLowerCase().includes(query);
				}));
		},
		async storeNewsItem() {
			if (this.isSaving) {
				return;
			}

			let parsedFormData = JSON.parse(JSON.stringify(this.formData));
			parsedFormData.degreeProgramShortCode =
				parsedFormData.degreeProgram?.value;
			delete parsedFormData.degreeProgram;

			this.isSaving = true;

			try {
				const response = await this.$refs.form.call(
					ApiNewsAdministration.storeNewsItem(parsedFormData),
				);

				if (response.meta.status !== 'success') {
					this.$fhcAlert.alertError(
						this.$capitalize(this.$p.t('ui', 'fehlerBeimSpeichern')),
					);
					return;
				}

				this.$fhcAlert.alertSuccess(
					this.$capitalize(this.$p.t('ui', 'gespeichert')),
				);
				this.$emit('created');
			} catch (error) {
				if (!error?.handled) {
					this.$fhcAlert.handleSystemError(error);
				}
			} finally {
				this.isSaving = false;
			}
		},
		async updateNewsItem() {
			if (!this.newsItem || this.isSaving) {
				return;
			}

			let parsedFormData = JSON.parse(JSON.stringify(this.formData));
			parsedFormData.degreeProgramShortCode =
				parsedFormData.degreeProgram?.value;
			delete parsedFormData.degreeProgram;

			this.isSaving = true;

			try {
				const response = await this.$refs.form.call(
					ApiNewsAdministration.updateNewsItem(
						this.newsItem.newsId,
						parsedFormData,
					),
				);

				if (response.meta.status !== 'success') {
					this.$fhcAlert.alertError(
						this.$capitalize(this.$p.t('ui', 'fehlerBeimSpeichern')),
					);
					return;
				}

				this.$fhcAlert.alertSuccess(
					this.$capitalize(this.$p.t('ui', 'gespeichert')),
				);
				this.$emit('update');
			} catch (error) {
				if (!error?.handled) {
					this.$fhcAlert.handleSystemError(error);
				}
			} finally {
				this.isSaving = false;
			}
		},
	},
	async created() {
		await this.$p.loadCategory(['global', 'ui', 'lehre']);

		let getAllDegreePrograms = await this.$api.call(
			ApiStudiengang.getDegreePrograms(),
		);
		if (getAllDegreePrograms.meta.status === 'success') {
			this.degreePrograms = getAllDegreePrograms.data.sort((a, b) =>
				a.bezeichnung.localeCompare(b.bezeichnung),
			);
		} else {
			this.$fhcAlert.alertError(
				this.$capitalize(this.$p.t('ui', 'fehlerBeimLesen')),
			);
		}

		this.fillFormData(this.newsItem);

		if (!this.$props.newsItem) {
			this.formData.degreeProgram =
				this.dropdownParsedDegreePrograms.find(
					(degreeProgram) => degreeProgram.value === 0,
				) ?? null;
		}
		console.log(FHC_JS_DATA_STORAGE_OBJECT);

		this.contentFormItems.addLanguage.config.languages = this.languagesToAdd;
	},
	template: /*html*/ `
	<section 
		class="card bg-white shadow-sm border-0 mb-4 overflow-x-hidden"
		aria-labelledby="news-item-form-heading"
	>
		<div class="card-body">
			<div class="d-flex justify-content-between align-items-center gap-3 mb-3">
				<h4 id="news-item-form-heading" ref="newsPageHeading" class="card-title fhc-primary-color mb-0">{{ $capitalize($p.t('ui', 'newsForm')) }}</h4>
				<button
					@click="$emit('toggle-preview')"
					type="button"
					class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center"
					:title="$capitalize(isPreviewShown ? $p.t('ui', 'hidePreview') : $p.t('ui', 'showPreview'))"
					:aria-label="$capitalize(isPreviewShown ? $p.t('ui', 'hidePreview') : $p.t('ui', 'showPreview'))"
					:aria-expanded="isPreviewShown"
					aria-controls="news-item-preview"
				>
					<i class="fa-solid" :class="isPreviewShown ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
				</button>
			</div>
			<core-form ref="form">
				<div class="d-flex flex-row flex-md-row align-items-md-end gap-3">
					<div class="d-flex flex-row flex-md-row align-items-md-end gap-3">
						<form-input
						:label="$capitalize($p.t('ui', 'visible') + ' ' + $p.t('ui', 'von'))"
						:teleport="true"
						:enable-time-picker="false"
						type="datePicker"
						name="visibleFrom"
						v-model="formData.visibleFrom"
						model-type="yyyy-MM-dd"
						format="dd.MM.yyyy"
						auto-apply
						/>
						<form-input
						:label="$capitalize($p.t('ui', 'visible') + ' ' + $p.t('global', 'bis'))"
						:teleport="true"
						:enable-time-picker="false"
						type="datePicker"
						name="visibleTo"
						v-model="formData.visibleTo"
						model-type="yyyy-MM-dd"
						format="dd.MM.yyyy"
						auto-apply
						/>
					</div>
					<div class="d-flex flex-row flex-md-row align-items-md-end gap-3">
						<form-input
							:label="$capitalize($p.t('lehre/studiengang'))"
							:suggestions="filteredDegreePrograms"
							:optionValue="(option) => option.value"
							:optionLabel="(option) => option.label"
							@complete="filterDegreePrograms($event)"
							type="autocomplete"
							name="degreeProgramShortCode"
							v-model="formData.degreeProgram"
 							dropdown  
							forceSelection
 							>
						</form-input>
						<form-input
							v-model="formData.semester"
							:label="$capitalize($p.t('lehre/studiensemester'))"
							type="select"
							name="semester"
							>
								<option
									v-for="semester in semesters"
									:key="semester.value"
									:value="semester.value"
									>
									{{ semester.label }}
								</option>
						</form-input>
					</div>
  				</div>
				<fhc-tabs
					:key="contentFormRenderKey"
					ref="tabs" 
					:useprimevue="true"
					:config="contentFormItems"
					:default="activeContentFormKey"
					v-model="formData.translations"
					@change="handleContentTabChange"
					@tab-action="handleContentTabAction"
					style="flex: 1 1 0%; height: 0%"
					class="mt-3"
					>
				</fhc-tabs>
				<div class="d-flex justify-content-end mt-2">
					<button
						v-if="activeContentLanguage && copySourceLanguages.length"
						@click="showCopyTranslationModal"
						type="button"
						class="btn btn-sm btn-outline-secondary"
						:title="$capitalize($p.t('ui', 'copyContentFromAnotherLanguage'))"
						aria-haspopup="dialog"
					>
						<i class="fa-solid fa-copy me-1" aria-hidden="true"></i>
						{{ $capitalize($p.t('ui', 'copyContentFromAnotherLanguage')) }}
					</button>
				</div>
				<news-item-copy-translation-modal
					ref="copyTranslationModal"
					:source-languages="copySourceLanguages"
					:target-language-label="getLanguageLabel(activeContentLanguage)"
					:target-has-content="activeTranslationHasContent"
					@copy="copyTranslation"
				></news-item-copy-translation-modal>
				<div v-if="!isSaving" class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
					<button
						@click="$emit('cancel')"
						type="button"
						class="btn btn-secondary"
					>
						{{ $capitalize($p.t('ui', 'abbrechen')) }}
					</button>
					<button
						@click="newsItem ? updateNewsItem() : storeNewsItem()"
						type="button"
						class="btn btn-primary"
					>
						{{ $capitalize($p.t('global', 'speichern')) }}
					</button>
				</div>
				<div v-else class="d-flex justify-content-end mt-4 pt-3 border-top" role="status">
					<span class="spinner-border" aria-hidden="true"></span>
				</div>
			</core-form>
		</div>
	</section>
    `,
};
