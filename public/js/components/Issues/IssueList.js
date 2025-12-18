import ApiIssueList from '../../api/factory/issueList.js';

export default {
	name: 'IssueList',
	emits: ['issuesLoaded'],
	 components: {
	 },
	 props: {
		person_id: Number,
		oe_kurzbz: String,
		fehlertyp_kurzbz: String,
		apps: [String, Array],
		behebung_parameter: Array,
		hauptzustaendig: {
			type: Boolean,
			default: false
		},
		date: null,
		endpoint: {
			type: Object,
			default: ApiIssueList
		}
	},
	 data() {
		return {
			title: "Issues",
			currentDate: null,
			isFetching: false,
			issues: null
		}
	},
	computed: {
		// if any property changes, get issues again
		propertiesChanged() {
		  return `${this.person_id}|${this.oe_kurzbz}|${this.fehlertyp_kurzbz}||${this.apps}|${this.behebung_parameter}`;
		},
	  },
	watch: {
		propertiesChanged(newVal, oldVal) {
			this.fetchIssues();
		},
	  },
	mounted() {
		this.currentDate = this.date || new Date();
		this.fetchIssues();
	},
	 methods: {

		 fetchIssues() {
			this.isFetching = true;
			this.$api.call(
				this.endpoint.getOpenIssuesByProperties(
					this.person_id,
					this.oe_kurzbz,
					this.fehlertyp_kurzbz,
					this.apps,
					this.behebung_parameter,
					this.hauptzustaendig
				)
			)
			.then(result => {
				this.issues = result.data;
				this.$emit('issuesLoaded', this.issues);
				this.isFetching = false;
			})
			.catch(this.$fhcAlert.handleSystemError);
		},
		formatDate(ds) {
			if (ds == undefined) return '';
			var d = new Date(ds);
			return d.getDate()  + "." + (d.getMonth()+1) + "." + d.getFullYear()
		}
	 },
	 template: `
		<div v-if="isFetching" class="spinner-border" role="status">
			<span class="visually-hidden">Loading...</span>
		</div>
		<div v-if="!isFetching && issues!=null && issues!=[]">
			<table class="table table-bordered">
				<tbody>
					<tr><th>Datum</th><th>Inhalt</th></tr>
					<tr v-for="(item, index) in issues" :key="item.issue_id">
						<td>{{ formatDate(item.datum) }}</td>
						<td>{{ item.inhalt }} <br>
							<slot name="additionalText" v-bind="item"></slot>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	 `
}
