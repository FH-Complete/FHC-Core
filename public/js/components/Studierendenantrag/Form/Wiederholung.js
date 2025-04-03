import {CoreFetchCmpt} from '../../Fetch.js';
import CoreForm from '../../Form/Form.js';
import FormValidation from '../../Form/Validation.js';


export default {
	components: {
		CoreFetchCmpt,
		CoreForm,
		FormValidation
	},
	emits: [
		'setInfos',
		'setStatus',
		'update:status'
	],
	props: {
		status: String,
		prestudentId: Number,
		studierendenantragId: Number
	},
	data() {
		return {
			data: null,
			saving: false,
			infos: []
		}
	},
	computed: {
		statusSeverity() {
			switch (this.data.status)
			{
				case 'Offen':
				case 'Erstellt':
				case 'ErsteAufforderungVersandt': return 'info';
				case 'Genehmigt': return 'success';
				case 'Pause':
				case 'Verzichtet':
				case 'Abgemeldet': return 'danger';
				default: return 'warning';
			}
		},
		datumPruefungFormatted() {
			if(!this.data.pruefungsdatum)
				return '';
			let datum = new Date(this.data.pruefungsdatum);
			return datum.toLocaleDateString();
		}
	},
	methods: {
		load() {
			return this.$fhcApi.factory
				.studstatus.wiederholung.getDetails(
					this.prestudentId
				)
				.then(result => {
					this.data = result.data;
					if (!this.data.status || this.data.status == 'ErsteAufforderungVersandt' || this.data.status == 'ZweiteAufforderungVersandt') {
						this.data.status = 'Offen';
						this.data.statustyp = this.$p.t('studierendenantrag', 'status_open');
					}
					this.$emit('update:status', this.data.status);
					const msg = (this.data.status == 'Pause' && this.data.status_insertvon == "Studienabbruch") ? Vue.computed(() => {
						let status = this.$p.t('studierendenantrag/status_stop');
						return this.$p.t('studierendenantrag', 'status_x', {status});
					}) : Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.data.statustyp}));
					this.$emit("setStatus", {
						msg,
						severity: this.statusSeverity
					});
					return result;
				});
		},
		createAntrag() {
			this.createAntragWithStatus(true);
		},
		cancelAntrag() {
			this.createAntragWithStatus(false);
		},
		createAntragWithStatus(repeat) {
			let func = repeat ? 'create' : 'cancel';
			let nextState = repeat ? 'Erstellt' : 'Verzichtet';

			this.$emit('setStatus', {
				msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_saving')})),
				severity: 'warning'
			});
			this.saving = true;

			this.$refs.form.factory
				.studstatus.wiederholung[func](
					this.data.prestudent_id,
					this.data.studiensemester_kurzbz
				)
				.then(result => {
					if (result.data === true)
						document.location += "";

					this.data = result.data;
					if (!this.data.status)
						this.data.status = nextState;
					this.$emit('update:status', this.data.status);
					this.$emit("setStatus", {
						msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.data.statustyp})),
						severity: this.statusSeverity
					});
					this.saving = false;
				})
				.catch(error => {
					this.$emit('setStatus', {
						msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_error')})),
						severity: 'danger'
					});
					this.saving = false;
					this.$fhcAlert.handleSystemError(error);
				});
		}
	},
	mounted() {
		this.infos = [...Array(5).keys()].map(n => ({
			body: Vue.computed(() => this.$p.t('studierendenantrag', 'infotext_Wiederholung_' + n))
		}));
		this.$emit('setInfos', this.infos);
	},
	template: `
	<div class="studierendenantrag-form-wiederholung">
		<core-fetch-cmpt :api-function="load">
			<core-form ref="form" class="row">
				<div class="col-12">
					<form-validation></form-validation>
					<table class="table">
						<tr>
							<th>{{$p.t('lehre', 'studiengang')}}</th>
							<td align="right">{{data.bezeichnung}}</td>
						</tr>
						<tr>
							<th>{{$p.t('lehre', 'organisationsform')}}</th>
							<td align="right">{{data.orgform_bezeichnung}}</td>
						</tr>
						<tr>
							<th>{{$p.t('projektarbeitsbeurteilung', 'nameStudierende')}}</th>
							<td align="right">{{data.name}}</td>
						</tr>
						<tr>
							<th>{{$p.t('person', 'personenkennzeichen')}}</th>
							<td align="right">{{data.matrikelnr}}</td>
						</tr>
						<tr>
							<th>{{$p.t('studierendenantrag', 'antrag_Wiederholung_pruefung')}}</th>
							<td align="right">{{data.lvbezeichnung}}</td>
						</tr>
						<tr>
							<th>{{$p.t('studierendenantrag', 'antrag_Wiederholung_pruefung_date')}}</th>
							<td align="right">{{datumPruefungFormatted}}</td>
						</tr>
					</table>
				</div>

				<div class="col-12 d-flex justify-content-end gap-2">
					<button
						v-if="!data.studierendenantrag_id || data.status == 'Offen'"
						type="button"
						class="btn btn-primary"
						@click="createAntrag"
						:disabled="saving"
					>
						{{$p.t('studierendenantrag/antrag_Wiederholung_button_yes')}}
					</button>
<!--					<button
						v-if="!data.studierendenantrag_id || data.status == 'Offen'"
						type="button"
						class="btn btn-danger"
						@click="cancelAntrag"
						:disabled="saving"
					>
						{{$p.t('studierendenantrag/antrag_Wiederholung_button_no')}}
					</button>-->
				</div>
			</core-form>
			<template v-slot:error="{errorMessage}">
				<div class="alert alert-danger m-0" role="alert">
					{{ errorMessage }}
				</div>
			</template>
		</core-fetch-cmpt>
	</div>
	`
}
