import Pagination from '../../../Pagination/Pagination.js';
import ApiNewsAdministration from '../../../../api/factory/newsAdministration.js';
import NewsListItem from './NewsListItem.js';
import NewsListFilter from './NewsListFilter.js';

export default {
	name: 'NewsList',
	emits: ['edit'],
	components: {
		NewsListItem,
		NewsListFilter,
		Pagination,
	},
	data() {
		return {
			newsItems: [],
			isLoading: false,
			loadError: null,
			maxPageCount: 0,
			page: 1,
			pageSize: 10,
			isPublishedFilter: 'all',
			isActiveFilter: true,
			degreeProgramShortCodeFilter: null,
			semesterFilter: null,
		};
	},
	watch: {
		'$p.user_language.value': function () {
			this.page = 1;
			this.fetchNews();
		},
	},
	computed: {
		sprache() {
			return this.$p.user_language.value;
		},
	},
	methods: {
		async getNewsItem(newsId) {
			await this.fetchNews();

			return this.newsItems.find(
				(news) => String(news.newsId) === String(newsId),
			);
		},
		async fetchNews() {
			this.isLoading = true;
			this.loadError = null;

			try {
				const response = await this.$api.call(
					ApiNewsAdministration.getNews(
						this.page,
						this.pageSize,
						this.sprache,
						this.isPublishedFilter,
						this.isActiveFilter,
						this.degreeProgramShortCodeFilter,
						this.semesterFilter,
					),
				);
				this.newsItems = this.parseNewsHtml(response.data);
				this.maxPageCount = response.meta.row_count;
			} catch (error) {
				this.newsItems = [];
				this.loadError = this.$capitalize(
					this.$p.t('ui', 'fehlerBeimLesen'),
				);
			} finally {
				this.isLoading = false;
			}
		},
		parseNewsHtml(html) {
			if (!html) {
				return [];
			}

			const document = new DOMParser().parseFromString(html, 'text/html');

			return Array.from(document.querySelectorAll('.news-list-item')).map(
				(article) => this.parseNewsItem(article),
			);
		},
		parseNewsItem(article) {
			const newsId = article
				.querySelector('[news-id]')
				?.getAttribute('news-id');
			const title = article.querySelector('.card-header h2');
			const author = article.querySelector('address');
			const time = article.querySelector('time');
			const isPublished = article.querySelector(
				'.card-header span[is-published]',
			);
			const content = article.querySelector('.card-text');
			console.log(content.title);
			return {
				newsId: newsId ? Number(newsId) : null,
				language: this.sprache,
				title: title ? title.textContent.trim() : '',
				author: author ? author.textContent.trim() : '',
				date: time ? time.textContent.trim() : '',
				dateTime: time ? time.getAttribute('datetime') || '' : '',
				content: content ? content.innerHTML : '',
				isPublished: isPublished
					? isPublished.getAttribute('is-published') === 'true'
					: null,
			};
		},
		afterPageUpdated(event) {
			this.page = event.page;
			this.pageSize = event.rows;
			this.fetchNews();
			this.$refs.newsListHeading.scrollIntoView({ block: 'end' });
		},
		afterFilterChanged(filters) {
			this.isPublishedFilter = filters.isPublished;
			this.isActiveFilter = filters.isActive;
			this.degreeProgramShortCodeFilter = filters.degreeProgramShortCode;
			this.semesterFilter = filters.semester;
			this.page = 1;
			this.fetchNews();
		},
	},
	created() {
		this.$p.loadCategory(['filter', 'global', 'lehre', 'ui']).then(() => {
			this.phrasesLoaded = true;
		});
		this.fetchNews();
	},
	template: /*html*/ `
	<section class="mt-4" aria-labelledby="news-administration-list-heading">
		<h3 id="news-administration-list-heading" ref="newsListHeading" class="fhc-primary-color">
			{{ $capitalize($p.t('ui', 'news')) }}
		</h3>
		<hr>
		<news-list-filter @filter-changed="afterFilterChanged"></news-list-filter>
		<pagination
			v-if="maxPageCount > pageSize"
			:page="page"
			:page_size="pageSize"
			:maxPageCount="maxPageCount"
			@pageUpdated="afterPageUpdated"
		></pagination>
		<div v-if="isLoading" class="d-flex justify-content-center py-4" role="status">
			<span class="spinner-border" aria-hidden="true"></span>
			<span class="visually-hidden">{{ $capitalize($p.t('ui', 'loadingNews')) }}</span>
		</div>
		<div v-else-if="loadError" class="alert alert-danger" role="alert">
			{{ loadError }}
		</div>
		<div v-else-if="!newsItems.length" class="alert alert-info" role="status">
			{{ $capitalize($p.t('ui', 'noNewsAvailable')) }}
		</div>
		<div v-else>
			<news-list-item
				v-for="(news, index) in newsItems"
				:key="news.newsId || page + '-' + index"
				:news="news"
				@edit="$emit('edit', $event)"
				@deleted="fetchNews()"
			></news-list-item>
		</div>
		<pagination
			v-if="maxPageCount > pageSize"
			:page="page"
			:page_size="pageSize"
			:maxPageCount="maxPageCount"
			@pageUpdated="afterPageUpdated"
		></pagination>
	</section>
	`,
};
