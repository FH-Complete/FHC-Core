import CoreForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';
import ApiVertrag from '../../../../../api/factory/stv/vertrag.js';

export default{
	name: "ProjektarbeitVertrag",
	components: {
		CoreForm,
		FormInput
	},
	emits: [
		'canceledVertrag',
		'vertragsstatusChanged'
	],
	props: {
		vertrag_id: Number,
		person_id: Number,
		betreuerProjektarbeit: Object
	},

	inject: {
		showVertragsdetails: {
			from: 'configShowVertragsdetails',
			default: false
		}
	},

	data() {
		return{
			data: {
				vertragsstatus: null,
				vertragsstunden: null,
				vertragsstunden_studiensemester_kurzbz: null
			},
			// status names for stages of Vertrag ("constants")
			vertragsstatus_akzeptiert: 'Akzeptiert',
			vertragsstatus_geaendert:'Geändert',
			vertragsstatus_storniert: 'Storniert',
		}
	},
	watch: {
		vertrag_id:
		{
			//deep: true,
			handler(newVal, oldVal) {
				this.setDefaultData();
				if (newVal !== null && newVal !== undefined) this.getVertrag();
			}
		},
	},
	computed: {
		vertragsstatus() {
			// not show Vertragsstatus if no data
			if (!this.data?.vertragsstatus || !this.betreuerProjektarbeit?.betreuerart_kurzbz) return;

			const betragVertrag = Number(this.data.betrag) || 0;
			const stundenVertrag = Number(this.data.vertragsstunden) || 0;

			const semStunden = Number(this.betreuerProjektarbeit.stunden) || 0;
			const stundensatz = Number(this.betreuerProjektarbeit.stundensatz) || 0;

			const kostenAktuell  = semStunden * stundensatz;

			// Vertragsstunden amount should be same as Semesterstunden amount, otherwise there has been a change
			let vertragsstatus = (stundenVertrag !== semStunden || betragVertrag !== kostenAktuell)
				? this.vertragsstatus_geaendert
				: (this.data.vertragsstatus || '');

			// vertragsstatus changed to "akzeptiert"
			this.$emit('vertragsstatusChanged', vertragsstatus == this.vertragsstatus_akzeptiert);

			return vertragsstatus;
		},
	},
	methods: {
		getVertrag() {
			if (this.showVertragsdetails === false)
				return;

			if (!this.vertrag_id)
				return;

			this.$api.call(ApiVertrag.getVertrag(this.vertrag_id))
				.then(result => {
					this.data = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},

		cancelVertrag() {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? {vertrag_id: this.vertrag_id, person_id: this.person_id}
					: Promise.reject({handled: true}))
				.then(result => {
					this.$api.call(ApiVertrag.cancelVertrag({vertrag_id: this.vertrag_id, person_id: this.person_id}))
				})
				.then(result => {
					this.setDefaultData();
					this.$emit('canceledVertrag');
					// vertragsstatus not  "akzeptiert" anymore
					this.$emit('vertragsstatusChanged', false);
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		setDefaultData() {
			this.data.vertragsstatus = null;
			this.data.vertragsstunden = null;
			this.data.vertragsstunden_studiensemester_kurzbz = null;
		}
	},
	template: `
		<core-form ref="form">
			<fieldset class="overflow-hidden" v-if="showVertragsdetails">
				<legend>
					{{$p.t('lehre', 'vertragsdetails')}}
				</legend>
				<div class="mb-3">
					{{ betreuerProjektarbeit?.betreuerart_kurzbz && betreuerProjektarbeit?.vertrag_id == null ? ' – '+$p.t('lehre', 'nochKeinVertrag') : '' }}
				</div>
				<div class="row align-items-end mb-3">
					<form-input
						:label="$p.t('lehre', 'vertragsstatus')"
						type="text"
						readonly
						v-model="vertragsstatus"
						:style="{fontWeight: vertragsstatus === this.vertragsstatus_geaendert ? 'bold' : 'normal'}"
						name="vertragsstatus"
					/>
				</div>
				{{$p.t('lehre', 'vertragurfassung')}}
				<div class="row mb-3">
					<form-input
						:label="$p.t('lehre', 'semesterstunden')"
						type="text"
						readonly
						v-model="data.vertragsstunden"
						name="vertragsstunden"
						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						:label="$p.t('lehre', 'studiensemester')"
						type="text"
						readonly
						v-model="data.vertragsstunden_studiensemester_kurzbz"
						name="vertragsstunden_studiensemester_kurzbz"
						>
					</form-input>
				</div>
				<div class="row mb-3" v-if="data?.vertragsstatus">
					<div class="col-12">
						<button
							type="button"
							class="btn btn-outline-secondary"
							:disabled="vertragsstatus == vertragsstatus_storniert"
							@click="cancelVertrag"
						>
							{{ $p.t('lehre', 'stornieren') }}
						</button>
					</div>
				</div>
			</fieldset>
		</core-form>
	`
};
