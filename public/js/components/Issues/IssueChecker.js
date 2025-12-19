export default {
	name: 'IssueChecker',
	expose: ['countPersonOpenIssues', 'checkPerson'],
	//emits: ['issuesLoaded'],
	 components: {
		"p-skeleton": primevue.skeleton,
	 },
	 props: {
		person_id: Number,
		//oe_kurzbz: String,
		apps: [String, Array],
		hauptzustaendig: {
			type: Boolean,
			default: false
		},
		endpoint: {
			type: Object,
			required: true
		}
	},
	 data() {
		return {
			title: "IssueChecker",
			currentDate: null,
			isFetching: false,
			openissuescount: null
		}
	},
	computed: {
	},
	watch: {
	},
	mounted() {
		this.countPersonOpenIssues();
	},
	 methods: {

		countPersonOpenIssues() {
			this.isFetching = true;
			this.$api.call(
				this.endpoint.countPersonOpenIssues(this.person_id, this.hauptzustaendig)
			)
			.then(result => {
				//this.$emit('issuesLoaded', this.issues);
				this.openissuescount = result.data.openissues;
				this.isFetching = false;
			})
			.catch(this.$fhcAlert.handleSystemError);
		},
		checkPerson() {
			this.isFetching = true;
			this.$api.call(
				this.endpoint.checkPerson(this.person_id, this.hauptzustaendig)
			)
			.then(result => {
				//this.$emit('issuesLoaded', this.issues);
				this.openissuescount = result.data.openissues;
				this.isFetching = false;
			})
			.catch(this.$fhcAlert.handleSystemError);
		},
	 },
	 template: `
		<div class="px-2">
			<h4 class="mb-1">Issues<a class="refresh-issues" title="erneut prüfen" href="javascript:void(0);" @click="checkPerson"><i class="fas fa-sync"></i></a></h4>
			<h6 v-if="!isFetching" class="text-muted">{{ openissuescount }}</h6>
			<h6 v-else class="mb-2"><p-skeleton v-if="isFetching" style="width:45%"></p-skeleton></h6>
		</div>
	 `
}
