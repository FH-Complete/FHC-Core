import ApiNewsAdministration from '../../../../api/factory/newsAdministration.js';

const createCollapseId = () => {
	const randomNumber = Math.floor(
		1000000000000000 + Math.random() * 8000000000000000,
	);
	return `news-administration-item-${randomNumber}`;
};

export default {
	name: 'NewsListItem',
	emits: ['edit', 'deleted'],
	props: {
		news: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			isExpanded: false,
			isDeleting: false,
			collapseId: createCollapseId(),
		};
	},
	methods: {
		toggleContent() {
			this.isExpanded = !this.isExpanded;
		},
		editNews(news) {
			this.$emit('edit', news);
		},
		async deleteNews(news) {
			if (this.isDeleting) {
				return;
			}

			if (
				(await this.$fhcAlert.confirm({
					message: this.$capitalize(this.$p.t('ui', 'frageSicherLoeschen')),
					acceptClass: 'btn btn-danger',
				})) === false
			) {
				return;
			}

			this.isDeleting = true;

			try {
				const response = await this.$api.call(
					ApiNewsAdministration.deleteNewsItem(news.newsId),
				);

				if (response.meta.status !== 'success') {
					return;
				}

				this.$emit('deleted', news);
			} catch (error) {
				this.$fhcAlert.handleSystemError(error);
			} finally {
				this.isDeleting = false;
			}
		},
	},
	created() {
		this.$p.loadCategory(['global', 'ui']).then(() => {
			this.phrasesLoaded = true;
		});
	},
	template: /*html*/ `
	<article
		class="card mb-3"
		:class="{'border-secondary': !news.isPublished || !news.isActive}"
		>
		<header
			class="card-header d-flex justify-content-between align-items-center gap-3"
			:class="{
				'fhc-primary': news.isPublished && news.isActive,
				'bg-secondary bg-opacity-25 text-secondary': !news.isPublished || !news.isActive,
			}"
			>
			<button
				type="button"
				class="btn btn-link text-reset text-decoration-none text-start d-flex align-items-center gap-2 flex-grow-1 p-0"
				:aria-expanded="isExpanded"
				:aria-controls="collapseId"
				@click="toggleContent"
				>
				<i
					class="fa-solid"
					:class="isExpanded ? 'fa-chevron-down' : 'fa-chevron-right'"
					aria-hidden="true"
				></i>
				<span class="h5 mb-0">{{ news.title }}</span>
				<span v-if="!news.isPublished" class="badge text-bg-secondary">
					{{ $capitalize($p.t('ui', 'notPublishedYet')) }}
				</span>
				<span v-if="!news.isActive" class="badge text-bg-secondary">
					{{ $capitalize($p.t('ui', 'noLongerActive')) }}
				</span>
			</button>
			<div class="d-flex align-items-center gap-2 flex-shrink-0">
				<div class="text-end">
					<address v-if="news.author" class="fw-bold small mb-0">
						{{ news.author }}
					</address>
					<time v-if="news.date" class="small" :datetime="news.dateTime">
						{{ news.date }}
					</time>
				</div>
				<div class="d-flex gap-1" :aria-label="$capitalize($p.t('global', 'actions'))">
					<button
						type="button"
						class="btn btn-sm rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center"
						:class="news.isPublished ? 'btn-light text-primary' : 'btn-outline-secondary bg-white'"
						style="width: 1.75rem; height: 1.75rem"
						:title="$capitalize($p.t('ui', 'bearbeiten'))"
						:aria-label="$capitalize($p.t('ui', 'bearbeiten'))"
						@click="editNews(news)"
					>
						<i class="fa-solid fa-pen" aria-hidden="true"></i>
					</button>
					<button
						type="button"
						class="btn btn-sm rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center"
						:class="news.isPublished ? 'btn-light text-danger' : 'btn-outline-danger bg-white'"
						style="width: 1.75rem; height: 1.75rem"
						:title="$capitalize($p.t('ui', 'loeschen'))"
						:aria-label="$capitalize($p.t('ui', 'loeschen'))"
						:disabled="isDeleting"
						@click="deleteNews(news)"
					>
						<i class="fa-solid fa-trash" aria-hidden="true"></i>
					</button>
				</div>
			</div>
		</header>
		<div v-show="isExpanded" :id="collapseId" class="card-body">
			<div class="card-text" v-html="news.content"></div>
		</div>
	</article>
	`,
};
