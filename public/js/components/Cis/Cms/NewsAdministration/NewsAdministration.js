import NewsItemForm from './NewsItemForm.js';
import NewsItemPreview from './NewsItemPreview.js';
import NewsList from './NewsList.js';
import ApiNewsAdministration from '../../../../api/factory/newsAdministration.js';

export default {
	name: 'NewsAdministration',
	components: {
		NewsItemForm,
		NewsItemPreview,
		NewsList,
	},
	data() {
		return {
			isNewsFormShown: false,
			isNewsPreviewShown: false,
			editableNewsItem: null,
			newsPreview: null,
			newsFormCollapse: null,
		};
	},
	watch: {
		isNewsFormShown: async function (isShown) {
			await this.$nextTick();
			this.newsFormCollapse?.[isShown ? 'show' : 'hide']();
		},
		'$route.query.newsId': {
			immediate: true,
			handler(newNewsId, oldNewsId) {
				if (!newNewsId) {
					this.isNewsFormShown = false;
					this.isNewsPreviewShown = false;
					this.editableNewsItem = null;
					this.newsPreview = null;
					return;
				}

				this.editNewsItem({ newsId: this.$route.query.newsId });
			},
		},
	},
	methods: {
		async scrollToNewsForm() {
			await this.$nextTick();
			this.$refs.newsItemForm?.$el?.scrollIntoView({
				behavior: 'smooth',
				block: 'start',
			});
		},
		async reloadNews() {
			await this.$refs.newsList.fetchNews();
		},
		async handleNewsItemUpdated() {
			this.isNewsFormShown = false;
			this.isNewsPreviewShown = false;
			this.editableNewsItem = null;
			this.newsPreview = null;
			await this.reloadNews();
		},
		handleNewsItemSaved() {
			this.reloadNews();
			this.isNewsFormShown = false;
			this.isNewsPreviewShown = false;
			this.editableNewsItem = null;
			this.newsPreview = null;
		},
		showCreateNewsForm() {
			this.editableNewsItem = null;
			this.isNewsPreviewShown = false;
			this.isNewsFormShown = true;
			this.scrollToNewsForm();
		},
		async loadNewsItem(newsId) {
			if (!newsId) {
				return;
			}

			try {
				const response = await this.$api.call(
					ApiNewsAdministration.getNewsItem(newsId),
				);
				const newsItem = response.data;

				if (!newsItem) {
					return null;
				}

				return newsItem;
			} catch (error) {
				this.$fhcAlert.handleSystemError(error);
			}
		},
		async editNewsItem(newsItem) {
			try {
				this.editableNewsItem = await this.loadNewsItem(newsItem.newsId);
				this.isNewsPreviewShown = false;

				await this.$nextTick();

				setTimeout(() => {
					this.isNewsFormShown = true;
					this.scrollToNewsForm();
				}, 100);
			} catch (error) {
				this.$fhcAlert.handleSystemError(error);
			}
		},
		cancelNewsForm() {
			this.isNewsFormShown = false;
			this.isNewsPreviewShown = false;
			this.editableNewsItem = null;
			this.newsPreview = null;
		},
		toggleNewsPreview() {
			this.isNewsPreviewShown = !this.isNewsPreviewShown;
		},
		handlePreviewChange(preview) {
			this.newsPreview = preview;
		},
	},
	created() {
		this.$p.loadCategory('ui').then(() => {
			this.phrasesLoaded = true;
		});
	},
	mounted() {
		this.newsFormCollapse = new bootstrap.Collapse(
			this.$refs.newsFormCollapse,
			{ toggle: false },
		);

		if (this.isNewsFormShown) {
			this.newsFormCollapse.show();
		}
	},
	beforeUnmount() {
		this.newsFormCollapse?.dispose();
	},
	template: /*html*/ `
	<div class="pb-3 overflow-x-hidden">
		<div class="d-flex justify-content-between align-items-center mb-3">
			<h2 ref="newsPageHeading" class="fhc-primary-color mb-0">{{ $capitalize($p.t('ui', 'newsAdministration')) }}</h2>
			<button
				v-if="!isNewsFormShown"
				@click="showCreateNewsForm"
				type="button"
				class="btn btn-primary rounded-circle d-inline-flex align-items-center justify-content-center p-0"
				style="width: 2.5rem; height: 2.5rem"
				:title="$capitalize($p.t('ui', 'createNews'))"
				:aria-label="$capitalize($p.t('ui', 'createNews'))"
				aria-controls="news-item-form-collapse"
			>
				<i class="fa-solid fa-plus" aria-hidden="true"></i>
			</button>
		</div>
		<div ref="newsFormCollapse" id="news-item-form-collapse" class="collapse">
			<div  class="row g-4 align-items-start">
				<div :class="isNewsPreviewShown ? 'col-12 col-xl-6' : 'col-12'">
					<news-item-form
						ref="newsItemForm"
						:key="editableNewsItem?.newsId || 'new-news-item'"
						id="news-item-form"
						:news-item="editableNewsItem"
						:is-preview-shown="isNewsPreviewShown"
						@created="handleNewsItemSaved"
						@update="handleNewsItemUpdated"
						@cancel="cancelNewsForm"
						@preview-change="handlePreviewChange"
						@toggle-preview="toggleNewsPreview"
					></news-item-form>
				</div>
				<div v-if="isNewsPreviewShown" class="col-12 col-xl-6">
					<news-item-preview
						:form-data="newsPreview?.formData"
						:active-language="newsPreview?.activeLanguage"
					></news-item-preview>
				</div>
			</div>
		</div>
		<news-list
			ref="newsList"
			@edit="editNewsItem"
		></news-list>
	</div>
	`,
};
