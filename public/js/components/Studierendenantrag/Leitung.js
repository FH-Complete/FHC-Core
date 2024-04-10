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
			axios.get(
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				'/components/Antrag/Leitung/getActiveStgs'
			).then(result => {
				this.stgs = result.data.retval;
			}).catch(error => {
				console.error(error);
			});
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
				axios
					.all(
						oks.map(
							antrag => axios.post(
								FHC_JS_DATA_STORAGE_OBJECT.app_root +
								FHC_JS_DATA_STORAGE_OBJECT.ci_router +
								'/components/Antrag/Leitung/approve' + antrag.typ,
								{
									studierendenantrag_id: antrag.studierendenantrag_id
								}
							)
						)
					)
					.then(this.showValidation)
					.catch(this.showError);
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
				axios
					.all(
						gruende.map(
							antrag => axios.post(
								FHC_JS_DATA_STORAGE_OBJECT.app_root +
								FHC_JS_DATA_STORAGE_OBJECT.ci_router +
								'/components/Antrag/Leitung/reject' + antrag.typ,
								{
									studierendenantrag_id: antrag.studierendenantrag_id,
									grund: antrag.grund
								}
							)
						)
					)
					.then(this.showValidation)
					.catch(this.showError);
			}
		},
		actionReopen(evt) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			axios
				.all(
					antraege.map(
						antrag => axios.post(
							FHC_JS_DATA_STORAGE_OBJECT.app_root +
							FHC_JS_DATA_STORAGE_OBJECT.ci_router +
							'/components/Antrag/Leitung/reopenAntrag/',
							{
								studierendenantrag_id: antrag.studierendenantrag_id
							}
						)
					)
				)
				.then(this.showValidation)
				.catch(this.showError);
		},
		actionPause(evt) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			axios
				.all(
					antraege.map(
						antrag => axios.post(
							FHC_JS_DATA_STORAGE_OBJECT.app_root +
							FHC_JS_DATA_STORAGE_OBJECT.ci_router +
							'/components/Antrag/Leitung/pauseAntrag/',
							{
								studierendenantrag_id: antrag.studierendenantrag_id
							}
						)
					)
				)
				.then(this.showValidation)
				.catch(this.showError);
		},
		actionUnpause(evt) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			axios
				.all(
					antraege.map(
						antrag => axios.post(
							FHC_JS_DATA_STORAGE_OBJECT.app_root +
							FHC_JS_DATA_STORAGE_OBJECT.ci_router +
							'/components/Antrag/Leitung/unpauseAntrag/',
							{
								studierendenantrag_id: antrag.studierendenantrag_id
							}
						)
					)
				)
				.then(this.showValidation)
				.catch(this.showError);
		},
		actionObject(evt) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			axios
				.all(
					antraege.map(
						antrag => axios.post(
							FHC_JS_DATA_STORAGE_OBJECT.app_root +
							FHC_JS_DATA_STORAGE_OBJECT.ci_router +
							'/components/Antrag/Leitung/objectAntrag/',
							{
								studierendenantrag_id: antrag.studierendenantrag_id
							}
						)
					)
				)
				.then(this.showValidation)
				.catch(this.showError);
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
				axios
					.all(
						gruende.map(
							antrag => axios.post(
								FHC_JS_DATA_STORAGE_OBJECT.app_root +
								FHC_JS_DATA_STORAGE_OBJECT.ci_router +
								'/components/Antrag/Leitung/objectionDeny/',
								{
									studierendenantrag_id: antrag.studierendenantrag_id,
									grund: antrag.grund
								}
							)
						)
					)
					.then(this.showValidation)
					.catch(this.showError);
			}
		},
		actionObjectionApprove(evt, gruende) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			axios
				.all(
					antraege.map(
						antrag => axios.post(
							FHC_JS_DATA_STORAGE_OBJECT.app_root +
							FHC_JS_DATA_STORAGE_OBJECT.ci_router +
							'/components/Antrag/Leitung/objectionApprove/',
							{
								studierendenantrag_id: antrag.studierendenantrag_id
							}
						)
					)
				)
				.then(this.showValidation)
				.catch(this.showError);
		},
		actionCancel(evt) {
			var antraege = evt || this.selectedData;
			this.$refs.loader.show();
			axios
				.all(
					antraege.map(
						antrag => axios.post(
							FHC_JS_DATA_STORAGE_OBJECT.app_root +
							FHC_JS_DATA_STORAGE_OBJECT.ci_router +
							'/components/Antrag/Abmeldung/cancelAntrag/',
							{
								antrag_id: antrag.studierendenantrag_id
							}
						)
					)
				)
				.then(this.showValidation)
				.catch(this.showError);
		},
		showValidation(results) {
			var errors = results.filter(res => res.data.error);
			this.$refs.loader.hide();
			if (errors.length) {
				let errorMsg = errors.map(
					error =>
					'Antrag ' +
					JSON.parse(error.config.data).studierendenantrag_id +
					'\n' +
					Object.values(error.data.retval).join('\n')
				).join('\n');

				BsAlert.popup(errorMsg, {dialogClass: 'alert alert-danger'});
			}
			this.reload();
		},
		showError(error) {
			this.$refs.loader.hide();
			let msg = error.response.data;
			if (msg.replace(/^\s+/, '').substr(0, 9) == '<!DOCTYPE' || msg.replace(/^\s+/, '').substr(0, 4).toLowerCase() == '<div')
				msg = error.message;
			BsAlert.popup(msg, {dialogClass: 'alert alert-danger'});
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
