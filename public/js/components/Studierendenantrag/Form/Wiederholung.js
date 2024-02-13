import {CoreFetchCmpt} from '../../Fetch.js';
import VueDatepicker from '../../vueDatepicker.js.php';

var _uuid = 0;

export default {
	components: {
		CoreFetchCmpt,
		VueDatepicker
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
			errors: {
				grund: [],
				default: []
			},
			siteUrl: FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router,
			infos: []
		}
	},
	computed: {
		statusSeverity() {
			switch (this.data.status)
			{
				case 'Erstellt': return 'info';
				case 'Genehmigt': return 'success';
				case 'Verzichtet':
				case 'Abgemeldet': return 'danger';
				default: return 'info';
			}
		},
		loadUrl() {
			return '/components/Antrag/Wiederholung/getDetailsForNewAntrag/' +
				this.prestudentId;
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
			return axios.get(
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				this.loadUrl
			).then(
				result => {
					this.data = result.data.retval;
					if (!this.data.status || this.data.status == 'ErsteAufforderungVersandt' || this.data.status == 'ZweiteAufforderungVersandt') {
						this.data.status = 'Offen';
						this.data.statustyp = this.$p.t('studierendenantrag', 'status_open');
					}
					this.$emit('update:status', this.data.status);
					this.$emit("setStatus", {
						msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.data.statustyp})),
						severity: this.statusSeverity
					});
					return result;
				}
			);
		},
		createAntrag() {
			this.createAntragWithStatus(true);
		},
		cancelAntrag() {
			this.createAntragWithStatus(false);
		},
		createAntragWithStatus(repeat) {
			let func = repeat ? 'createAntrag' : 'cancelAntrag';
			let nextState = repeat ? 'Erstellt' : 'Verzichtet';

			this.$emit('setStatus', {
				msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_saving')})),
				severity: 'warning'
			});
			this.saving = true;
			for(var k in this.errors)
				this.errors[k] = [];

			axios.post(
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				'/components/Antrag/Wiederholung/' + func + '/',
				{
					prestudent_id: this.data.prestudent_id,
					studiensemester: this.data.studiensemester_kurzbz
				}
			).then(
				result => {
					if (result.data.error)
					{
						for (var k in result.data.retval)
						{
							if (this.errors[k] !== undefined)
								this.errors[k].push(result.data.retval[k]);
							else
								this.errors.default.push(result.data.retval[k]);
						}
						this.$emit('setStatus', {
							msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_error')})),
							severity: 'danger'
						});
					}
					else
					{
						if (result.data.retval === true)
							document.location += "";
						this.data = result.data.retval;
						if (!this.data.status)
							this.data.status = nextState;
						this.$emit('update:status', this.data.status);
						this.$emit("setStatus", {
							msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.data.statustyp})),
							severity: this.statusSeverity
						});
					}
					this.saving = false;
				}
			);
		}
	},
	created() {
		this.uuid = _uuid++;
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
			<div class="row">
				<div class="col-12">
					<div v-for="error in errors.default" class="alert alert-danger" role="alert" v-html="error">
					</div>
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
			</div>
			<template v-slot:error="{errorMessage}">
				<div class="alert alert-danger m-0" role="alert">
					{{ errorMessage }}
				</div>
			</template>
		</core-fetch-cmpt>
	</div>
	`
}
