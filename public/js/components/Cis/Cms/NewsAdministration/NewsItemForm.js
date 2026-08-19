import CoreForm from '../../../Form/Form.js';
import FormInput from '../../../Form/Input.js';
import FhcTabs from '../../../Tabs.js';
import NewsItemAddLanguageModal from './NewsItemAddLanguageModal.js';
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
		NewsItemAddLanguageModal,
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
		news: {
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
			availableLanguages: [
				{ label: 'German', value: 'German' },
				{ label: 'English', value: 'English' },
				{ label: 'French', value: 'French' },
				{ label: 'Spanish', value: 'Spanish' },
			],
			semesters: [
				{
					label: 'Alle Semester',
					value: null,
				},
				{
					label: '1. Semester',
					value: '1',
				},
				{
					label: '2. Semester',
					value: '2',
				},
				{
					label: '3. Semester',
					value: '3',
				},
				{
					label: '4. Semester',
					value: '4',
				},
				{
					label: '5. Semester',
					value: '5',
				},
				{
					label: '6. Semester',
					value: '6',
				},
				{
					label: '7. Semester',
					value: '7',
				},
				{
					label: '8. Semester',
					value: '8',
				},
			],
			formData: {
				visibleFrom: null,
				visibleTo: null,
				degreeProgram: null,
				semester: null,
				translations: [
					{
						language: 'German',
						author: '',
						title: '',
						text: '',
						isPublished: false,
					},
				],
			},
			contentFormItems: {
				germanContentForm: {
					title: 'German Content Form',
					component: BASE_COMPONENT_URL + 'NewsItemContentForm.js',
					config: {
						language: 'German',
						type: 'news',
					},
					key: 'germanContentForm',
				},
				addLanguage: {
					title: '+',
					component: BASE_COMPONENT_URL + 'NewsItemAddLanguageTab.js',
					config: { hasAvailableLanguages: true },
					key: 'addLanguage',
				},
			},
		};
	},
	watch: {
		'$p.user_language.value': function (sprache) {
			this.fetchNews();
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
				title: `${language} Content Form`,
				component: BASE_COMPONENT_URL + 'NewsItemContentForm.js?124',
				config: {
					language,
					type: 'news',
				},
				key,
			};
		},
		showLanguageModal() {
			this.$refs.addLanguageModal.show();
		},
		showCopyTranslationModal() {
			this.$refs.copyTranslationModal.show();
		},
		getLanguageLabel(language) {
			return (
				this.availableLanguages.find(
					(availableLanguage) => availableLanguage.value === language,
				)?.label ?? language
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
			if (key === 'addLanguage') {
				this.showLanguageModal();
				return;
			}

			this.activeContentFormKey = key;
		},
		handleContentTabAction({ key, payload }) {
			if (key === 'addLanguage') {
				this.showLanguageModal();
				return;
			}

			if (payload?.action === 'remove-language') {
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
			this.$refs.addLanguageModal.hide();
			this.$nextTick(() => this.$refs.tabs.change(key));
		},
		restoreActiveContentTab() {
			this.$refs.tabs.change(this.activeContentFormKey);
		},
		fillFormData(news) {
			if (!news) {
				return;
			}

			this.formData.visibleFrom =
				news.visibleFrom ?? news.dateTime?.slice(0, 10) ?? null;
			this.formData.visibleTo = news.visibleTo ?? null;
			const degreeProgramShortCode =
				news.degreeProgramShortCode ?? news.degreeProgram?.value ?? null;
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
			this.formData.semester = news.semester ?? null;

			const translations = news.translations ?? [
				{
					language: news.language,
					author: news.author,
					title: news.title,
					text: news.content,
					isPublished: news.isPublished,
				},
			];

			translations.forEach((sourceTranslation) => {
				const translation = this.formData.translations.find(
					(item) => item.language === sourceTranslation.language,
				);

				if (translation) {
					Object.assign(translation, sourceTranslation);
					return;
				}

				this.formData.translations.push({
					language: sourceTranslation.language,
					author: sourceTranslation.author ?? '',
					title: sourceTranslation.title ?? '',
					text: sourceTranslation.text ?? '',
					isPublished: Boolean(sourceTranslation.isPublished),
				});
			});
		},
		filterDegreePrograms(event) {
			let defaultItem = {
				label: this.$p.t('ui', 'dropdownEmptyOption'),
				value: null,
			};

			const query = event.query.toLowerCase();
			if (!query) {
				return (this.filteredDegreePrograms = [
					defaultItem,
					...this.dropdownParsedDegreePrograms,
				]);
			}

			return (this.filteredDegreePrograms = [defaultItem]
				.concat(this.dropdownParsedDegreePrograms)
				.filter((unit) => {
					return unit.label.toLowerCase().includes(query);
				}));
		},
		async storeNewsItem() {
			let parsedFormData = JSON.parse(JSON.stringify(this.formData));
			parsedFormData.degreeProgramShortCode =
				parsedFormData.degreeProgram?.value;
			delete parsedFormData.degreeProgram;

			let response;
			try {
				response = await this.$refs.form.call(
					ApiNewsAdministration.storeNewsItem(parsedFormData),
				);
			} catch (error) {
				console.error('Error storing news item:', error);
				this.$fhcAlert.handleSystemError(error);
				return;
			}
			if (response.meta.status !== 'success') {
				this.$fhcAlert.alertError(this.$p.t('ui', 'errorStoringNewsItem'));
				return;
			}

			this.$fhcAlert.alertSuccess(
				this.$p.t('ui', 'newsItemStoredSuccessfully'),
			);
			this.$emit('created');
		},
		async updateNewsItem() {
			if (!this.news || this.isSaving) {
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
						this.news.newsId,
						parsedFormData,
					),
				);

				if (response.meta.status !== 'success') {
					this.$fhcAlert.alertError(this.$p.t('ui', 'errorStoringNewsItem'));
					return;
				}

				this.$fhcAlert.alertSuccess(
					this.$p.t('ui', 'newsItemStoredSuccessfully'),
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
		this.fillFormData(this.news);

		let getAllDegreePrograms = await this.$api.call(
			ApiStudiengang.getDegreePrograms(),
		);
		if (getAllDegreePrograms.meta.status === 'success') {
			this.degreePrograms = getAllDegreePrograms.data.sort((a, b) =>
				a.bezeichnung.localeCompare(b.bezeichnung),
			);
			this.fillFormData(this.news);
		} else {
			this.$fhcAlert.alertError(this.$p.t('ui', 'errorFetchingDegreePrograms'));
		}

		console.log(FHC_JS_DATA_STORAGE_OBJECT);
	},
	template: /*html*/ `
	<section
		:class="{'pb-3': isMobile}"
		class="card bg-white shadow-sm border-0 mb-4 overflow-x-hidden"
		aria-labelledby="news-item-form-heading"
	>
		<div class="card-body">
			<div class="d-flex justify-content-between align-items-center gap-3 mb-3">
				<h4 id="news-item-form-heading" ref="newsPageHeading" class="card-title fhc-primary-color mb-0">News Form</h4>
				<button
					@click="$emit('toggle-preview')"
					type="button"
					class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center"
					:title="isPreviewShown ? 'Hide preview' : 'Show preview'"
					:aria-label="isPreviewShown ? 'Hide preview' : 'Show preview'"
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
						:label="$p.t('ui', 'visible') + ' ' + $p.t('ui', 'von')"
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
						:label="$p.t('ui', 'visible') + ' ' + $p.t('global', 'bis')"
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
						:title="copySourceLanguages.length ? 'Copy content from another language' : 'Add another language to copy content'"
						aria-haspopup="dialog"
					>
						<i class="fa-solid fa-copy me-1" aria-hidden="true"></i>
						Copy content from another language
					</button>
				</div>
				<news-item-add-language-modal
					ref="addLanguageModal"
					:languages="languagesToAdd"
					@add-language="addLanguage"
					@hidden="restoreActiveContentTab"
				></news-item-add-language-modal>
				<news-item-copy-translation-modal
					ref="copyTranslationModal"
					:source-languages="copySourceLanguages"
					:target-language-label="getLanguageLabel(activeContentLanguage)"
					:target-has-content="activeTranslationHasContent"
					@copy="copyTranslation"
				></news-item-copy-translation-modal>
				<div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
					<button
						@click="$emit('cancel')"
						type="button"
						class="btn btn-secondary"
					>
						{{$p.t('ui', 'abbrechen')}}
					</button>
					<button
						@click="news ? updateNewsItem() : storeNewsItem()"
						type="button"
						class="btn btn-primary"
						:disabled="isSaving"
					>
						{{$p.t('global', 'speichern')}}
					</button>
				</div>
			</core-form>
		</div>
	</section>
    `,
};
