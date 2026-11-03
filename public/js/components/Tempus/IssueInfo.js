import ApiTempusIssues from '../../api/factory/tempus/issues.js';

export default {
	name: 'TempusIssueInfo',
	emits: ['open'],
	data() {
		return {
			anzahl: null,
			isFetching: false,
		};
	},
	mounted() {
		this.load();
	},
	methods: {
		load() {
			this.isFetching = true;
			return this.$api
				.call(ApiTempusIssues.countOpenIssues())
				.then(result => {
					this.anzahl = result.data.openissues;
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.isFetching = false;
				});
		},
	},
	template: `
		<div class="d-inline-flex align-items-center gap-1">
			<button
				type="button"
				class="btn btn-sm"
				:class="anzahl ? 'btn-outline-warning' : 'btn-outline-secondary'"
				title="Offene Kollisionen anzeigen"
				:disabled="isFetching"
				@click="$emit('open')"
			>
				<i class="fa-solid fa-triangle-exclamation"></i>
				<span v-if="isFetching" class="spinner-border spinner-border-sm ms-1" role="status"></span>
				<span v-else class="ms-1">{{ anzahl ?? '-' }}</span>
			</button>
		</div>
	`
};