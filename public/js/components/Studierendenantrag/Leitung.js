import LeitungHeader from './Leitung/Header.js';
import LeitungActions from './Leitung/Actions.js';
import LeitungTable from './Leitung/Table.js';
import GrundPopup from './Leitung/GrundPopup.js';
import LvPopup from './Leitung/LvPopup.js';
import BsAlert from '../Bootstrap/Alert.js';
import FhcLoader from '../Loader.js';

import ApiStudstatusLeitung from '../../api/factory/studstatus/leitung.js';
import ApiStudstatusAbmeldung from '../../api/factory/studstatus/abmeldung.js';

export default {
	components: {
		LeitungHeader,
		LeitungTable,
		LeitungActions,
		FhcLoader
	},
	props: {
		stgL: Array,
		stgA: Array
	},
	data() {
		return {
			filter: undefined,
			selectedData: [],
			columns: [],
			stgs: []
		}
	},
	computed: {
		stgkzL() {
			if (!this.stgL)
				return [];
			return this.stgL.map(stg => parseInt(stg));
		},
		stgkzA() {
			if (!this.stgA)
				return [];
			return this.stgA.map(stg => parseInt(stg));
		}
	},
	methods: {
		loadFilter() {
			this.$api
				.call(ApiStudstatusLeitung.getStgs())
				.then(result => this.stgs = result.data)
				.catch(this.$fhcAlert.handleSystemError);
		},
		changeFilter(filter) {
			this.filter = filter || undefined;
			this.reload();
		},
		reload() {
			if (this.$refs.table)
				this.$refs.table.reload(this.filter);
			this.loadFilter();
		},
		download() {
			this.$refs.table.download();
		},
		actionApprove(evt, oks) {
			var antraege = evt || [...this.selectedData];

			if (!oks) {
				oks = [];
			}
			var currentAntrag = antraege.shift();
			if (currentAntrag) {
				if (currentAntrag.typ != 'Wiederholung')
				{
					oks.push(currentAntrag);
					this.actionApprove(antraege, oks);
				}
				else
				{
					let countAntrage = 0;
					LvPopup
						.popup(this.$p.t('studierendenantrag','title_show_lvs', currentAntrag), {
							antragId: currentAntrag.studierendenantrag_id,
							footer: true,
							dialogClass: 'modal-lg',
							countRemaining : antraege.filter(antrag => antrag.typ == 'Wiederholung').length
						})
						.then(result => {
							if (result[0]) {
								oks.push(currentAntrag);
								if (result[1])
									while (antraege.length)
										oks.push(antraege.pop());
							} else if (result[1]) {
								while (antraege.length) {
									currentAntrag = antraege.pop();
									if (currentAntrag.typ != 'Wiederholung')
										oks.push(currentAntrag);
								}
							}
							this.actionApprove(antraege, oks);
						})
						.catch(() => {});
				}
			} else {
				this.$refs.loader.show();
				this
					._singleOrMultiApiCall(oks, ApiStudstatusLeitung.approve)
					.then(this.showResults);
			}
		},
		actionReject(evt, gruende) {
			var antraege = evt || this.selectedData;
			if (!gruende)
				gruende = [];
			var currentAntrag = antraege.pop();
			if (currentAntrag) {
				GrundPopup
					.popup(this.$p.t('studierendenantrag', 'title_grund', {id: currentAntrag.studierendenantrag_id}), {
						countRemaining: antraege.length
					})
					.then(result => {
						currentAntrag.grund = result[0];
						gruende.push(currentAntrag);
						if (result[1])
						{
							while (antraege.length)
							{
								currentAntrag = antraege.pop();
								currentAntrag.grund = result[0];
								gruende.push(currentAntrag);
							}
						}
						this.actionReject(antraege, gruende);
					})
					.catch(() => {});
			} else {
				this.$refs.loader.show();

				this
					._singleOrMultiApiCall(gruende, ApiStudstatusLeitung.reject)
					.then(this.showResults);
			}
		},
		actionReopen(evt) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			this
				._singleOrMultiApiCall(antraege, ApiStudstatusLeitung.reopen)
				.then(this.showResults);
		},
		actionPause(evt) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			this
				._singleOrMultiApiCall(antraege, ApiStudstatusLeitung.pause)
				.then(this.showResults);
		},
		actionUnpause(evt) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			this
				._singleOrMultiApiCall(antraege, ApiStudstatusLeitung.unpause)
				.then(this.showResults);
		},
		actionObject(evt) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			this
				._singleOrMultiApiCall(antraege, ApiStudstatusLeitung.object)
				.then(this.showResults);
		},
		actionoObjectionDeny(evt, gruende) {
			var antraege = evt || this.selectedData;
			if (!gruende)
				gruende = [];
			var currentAntrag = antraege.pop();
			if (currentAntrag) {
				GrundPopup
					.popup(this.$p.t('studierendenantrag', 'title_grund', {id: currentAntrag.studierendenantrag_id}), {
						countRemaining : antraege.length,
						optional: true
					})
					.then(result => {
						currentAntrag.grund = result[0];
						gruende.push(currentAntrag);
						if (result[1]) {
							while (antraege.length) {
								currentAntrag = antraege.pop();
								currentAntrag.grund = result[0];
								gruende.push(currentAntrag);
							}
						}
						this.actionoObjectionDeny(antraege, gruende);
					})
					.catch(() => {});
			} else {
				this.$refs.loader.show();
				this
					._singleOrMultiApiCall(gruende, ApiStudstatusLeitung.denyObjection)
					.then(this.showResults);
			}
		},
		actionObjectionApprove(evt, gruende) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			this
				._singleOrMultiApiCall(antraege, ApiStudstatusLeitung.approveObjection)
				.then(this.showResults);
		},
		actionCancel(evt) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			if (Array.isArray(antraege)) {
				Promise
					.allSettled(
						antraege.map(antrag => this.$api.call(
							ApiStudstatusAbmeldung.cancel(antrag.studierendenantrag_id),
							{ errorHeader: '#' + antrag.studierendenantrag_id }
						))
					)
					.then(this.showResults);
			} else {
				this.$api
					.call(ApiStudstatusAbmeldung.cancel(antraege))
					.then(this.showResults);
			}
		},
		showResults(results) {
			let fulfilled = results.filter(res => res.status == 'fulfilled');
			this.$refs.loader.hide();
			//fulfilled.forEach(a => this.$fhcAlert.alertDefault('success', '#' + a.value.data, 'Approved, ...'));
			if (fulfilled.length)
				this.reload();
		},
		_singleOrMultiApiCall(antraege, endpoint) {
			if (Array.isArray(antraege)) {
				return Promise
					.allSettled(antraege.map(antrag => this.$api.call(
						endpoint(antrag),
						{ errorHeader: '#' + antrag.studierendenantrag_id }
					)));
			}
			return this.$api.call(endpoint(antraege));
		}
	},
	created() {
		this.loadFilter();
	},
	template: `
	<div class="studierendenantrag-leitung fhc-table">
		<leitung-header
			:stgs="stgs"
			@input="changeFilter"
			>
		</leitung-header>

		<leitung-actions
			:stg-a="stgkzA"
			:stg-l="stgkzL"
			:selectedData="selectedData"
			:columns="columns"
			@reload="reload"
			@action:approve="actionApprove"
			@action:reject="actionReject"
			@action:reopen="actionReopen"
			@download="download"
			>
		</leitung-actions>

		<leitung-table
			ref="table"
			:stg-a="stgkzA"
			:stg-l="stgkzL"
			:filter="filter"
			v-model:columnData="columns"
			v-model:selectedData="selectedData"
			@action:approve="actionApprove"
			@action:reject="actionReject"
			@action:reopen="actionReopen"
			@action:object="actionObject"
			@action:objectionDeny="actionoObjectionDeny"
			@action:objectionApprove="actionObjectionApprove"
			@action:cancel="actionCancel"
			@action:pause="actionPause"
			@action:unpause="actionUnpause"
			@reload="reload"
			>
		</leitung-table>

		<fhc-loader ref="loader"></fhc-loader>
	</div>
	`
}
