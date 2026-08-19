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
			newsToEdit: null,
			newsPreview: null,
		};
	},
	methods: {
		async reloadNews() {
			await this.$refs.newsList.reload();
		},
		async handleNewsItemUpdated() {
			this.isNewsFormShown = false;
			this.isNewsPreviewShown = false;
			this.newsToEdit = null;
			this.newsPreview = null;
			await this.reloadNews();
		},
		handleNewsItemSaved() {
			this.reloadNews();
			this.isNewsFormShown = false;
			this.isNewsPreviewShown = false;
			this.newsToEdit = null;
			this.newsPreview = null;
		},
		showCreateNewsForm() {
			this.newsToEdit = null;
			this.isNewsPreviewShown = false;
			this.isNewsFormShown = true;
		},
		async editNews(news) {
			console.log('Editing news item:', news);
			try {
				const response = await this.$api.call(
					ApiNewsAdministration.getNewsItem(news.newsId),
				);
				const freshNews = response.data;

				if (!freshNews) {
					return;
				}

				this.newsToEdit = freshNews;
				this.isNewsPreviewShown = false;
				this.isNewsFormShown = true;
			} catch (error) {
				this.$fhcAlert.handleSystemError(error);
			}
		},
		cancelNewsForm() {
			this.isNewsFormShown = false;
			this.isNewsPreviewShown = false;
			this.newsToEdit = null;
			this.newsPreview = null;
		},
		toggleNewsPreview() {
			this.isNewsPreviewShown = !this.isNewsPreviewShown;
		},
		handlePreviewChange(preview) {
			this.newsPreview = preview;
		},
	},
	template: /*html*/ `
	<div :class="{'pb-3': isMobile}" class="overflow-x-hidden">
		<div class="d-flex justify-content-between align-items-center mb-3">
			<h2 ref="newsPageHeading" class="fhc-primary-color mb-0">News Administration</h2>
			<button
				v-if="!isNewsFormShown"
				@click="showCreateNewsForm"
				type="button"
				class="btn btn-primary rounded-circle d-inline-flex align-items-center justify-content-center p-0"
				style="width: 2.5rem; height: 2.5rem"
				:title="$p.t('news', 'createNews')"
				:aria-label="$p.t('news', 'createNews')"
				aria-controls="news-item-form"
			>
				<i class="fa-solid fa-plus" aria-hidden="true"></i>
			</button>
		</div>
		<Transition>
			<div v-if="isNewsFormShown" class="row g-4 align-items-start">
				<div :class="isNewsPreviewShown ? 'col-12 col-xl-6' : 'col-12'">
					<news-item-form
						:key="newsToEdit?.newsId || 'new-news-item'"
						id="news-item-form"
						:news="newsToEdit"
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
		</Transition>
		<news-list
			ref="newsList"
			@edit="editNews"
		></news-list>
	</div>
	`,
};
