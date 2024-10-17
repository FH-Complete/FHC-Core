import LeitungHeader from './Leitung/Header.js';
import LeitungActions from './Leitung/Actions.js';
import LeitungTable from './Leitung/Table.js';
import GrundPopup from './Leitung/GrundPopup.js';
import LvPopup from './Leitung/LvPopup.js';
import BsAlert from '../Bootstrap/Alert.js';
import FhcLoader from '../Loader.js';

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
			this.$fhcApi.factory
				.studstatus.leitung.getStgs()
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
				this.$fhcApi.factory
					.studstatus.leitung.approve(oks)
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
				this.$fhcApi.factory
					.studstatus.leitung.reject(gruende)
					.then(this.showResults);
			}
		},
		actionReopen(evt) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			this.$fhcApi.factory
				.studstatus.leitung.reopen(gruende)
				.then(this.showResults);
		},
		actionPause(evt) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			this.$fhcApi.factory
				.studstatus.leitung.pause(antraege)
				.then(this.showResults);
		},
		actionUnpause(evt) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			this.$fhcApi.factory
				.studstatus.leitung.unpause(antraege)
				.then(this.showResults);
		},
		actionObject(evt) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			this.$fhcApi.factory
				.studstatus.leitung.object(antraege)
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
				this.$fhcApi.factory
					.studstatus.leitung.denyObjection(gruende)
					.then(this.showResults);
			}
		},
		actionObjectionApprove(evt, gruende) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			this.$fhcApi.factory
				.studstatus.leitung.approveObjection(antraege)
				.then(this.showResults);
		},
		actionCancel(evt) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			this.$fhcApi.factory
				.studstatus.abmeldung.cancel(antraege)
				.then(this.showResults);
		},
		showResults(results) {
			let fulfilled = results.filter(res => res.status == 'fulfilled');
			this.$refs.loader.hide();
			//fulfilled.forEach(a => this.$fhcAlert.alertDefault('success', '#' + a.value.data, 'Approved, ...'));
			if (fulfilled.length)
				this.reload();
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
