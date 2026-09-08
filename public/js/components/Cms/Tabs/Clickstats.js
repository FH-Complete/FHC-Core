import ApiCmsAdmin from '../../../api/factory/cmsadmin.js';

export default {
	name: 'CmsClickstats',
	props: {
		contentId: Number,
		sprache: String,
		version: Number,
		contentInfo: Object
	},
	emits: ['select-content'],
	data() {
		return {
			own: null,
			ranked: [],
			// The ranking stays absent until the editor asks for it, so an empty table and
			// a table that was never fetched stay apart.
			rankingLoaded: false,
			since: null,
			months: 12,
			loadingOwn: false,
			loadingRanking: false
		};
	},
	watch: {
		contentId() { this.loadOwn(); },
		sprache() { this.loadOwn(); }
	},
	methods: {
		// One counting query. Cheap enough to run whenever the tab or the period opens.
		loadOwn() {
			if (this.contentId == null) return;

			this.loadingOwn = true;
			this.$api
				.call(ApiCmsAdmin.getClickCount(this.contentId, this.months))
				.then(result => {
					this.own = result.data.own;
					this.since = result.data.since;
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => { this.loadingOwn = false; });
		},

		// Groups the whole log, which takes seconds. Only on request.
		loadRanking() {
			this.loadingRanking = true;
			this.$api
				.call(ApiCmsAdmin.getClickRanking(this.months, this.sprache))
				.then(result => {
					this.ranked = result.data.ranked || [];
					this.since = result.data.since;
					this.rankingLoaded = true;
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => { this.loadingRanking = false; });
		},

		// A new period always refreshes the cheap number. The ranking follows only once it
		// is on screen, so changing the period never costs the long wait unasked.
		onPeriodChange() {
			this.loadOwn();
			if (this.rankingLoaded) this.loadRanking();
		},

		// since arrives as an SQL timestamp. Format it the way the other tabs do.
		formatDate(value) {
			if (!value) return '';
			const dt = luxon.DateTime.fromSQL(value);
			return dt.isValid ? dt.toFormat('dd.MM.yyyy') : value;
		},

		isCurrent(row) {
			return row.content_id === this.contentId;
		},

		onContentClick(content_id) {
			this.$emit('select-content', content_id);
		}
	},
	created() {
		this.monthOptions = [3, 6, 12, 24, 0];
	},
	mounted() {
		this.loadOwn();
	},
	template: `
		<div class="p-3" v-if="contentId != null">
			<div class="d-flex gap-2 align-items-center mb-3">
				<label class="form-label mb-0">{{ $p.t('cms/zeitraum') }}</label>
				<select class="form-select form-select-sm"
					style="max-width: 220px"
					v-model.number="months"
					:disabled="loadingOwn || loadingRanking"
					@change="onPeriodChange">
					<option v-for="m in monthOptions" :key="m" :value="m">
						{{ m ? $p.t('cms/letzteNMonate', [m]) : $p.t('cms/gesamterZeitraum') }}
					</option>
				</select>
				<i v-if="loadingOwn" class="fa-solid fa-spinner fa-spin"></i>
			</div>

			<div class="form-text mb-3">
				{{ since
					? $p.t('cms/klicksSeit', [formatDate(since)])
					: $p.t('cms/klicksGesamt') }}
			</div>

			<div class="mb-4">
				<h5>{{ $p.t('cms/aufrufeDiesesEintrags') }}</h5>
				<span class="fs-4">{{ own === null ? '-' : own }}</span>
			</div>

			<h5>{{ $p.t('cms/meistAufgerufeneEintraege') }}</h5>

			<button v-if="!rankingLoaded"
				class="btn btn-outline-primary"
				:disabled="loadingRanking"
				@click="loadRanking">
				<i v-if="loadingRanking" class="fa-solid fa-spinner fa-spin me-1"></i>
				{{ $p.t('cms/alleKlickstatistikenLaden') }}
			</button>

			<table v-else-if="ranked.length > 0" class="table table-sm table-striped">
				<thead>
					<tr>
						<th>{{ $p.t('cms/rang') }}</th>
						<th>ID</th>
						<th>{{ $p.t('cms/titel') }}</th>
						<th>{{ $p.t('cms/aufrufe') }}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(row, idx) in ranked" :key="row.content_id"
						:class="isCurrent(row) ? 'table-primary' : ''">
						<td>{{ idx + 1 }}</td>
						<td>{{ row.content_id }}</td>
						<td>
							<a href="#" @click.prevent="onContentClick(row.content_id)">
								{{ row.titel || $p.t('cms/ohneTitel') }}
							</a>
						</td>
						<td>{{ row.hits }}</td>
					</tr>
				</tbody>
			</table>

			<div v-else class="text-muted">
				{{ $p.t('cms/keineAufrufeImZeitraum') }}
			</div>
		</div>
	`
};
