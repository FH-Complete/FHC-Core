import {CoreFilterCmpt} from "../../filter/Filter.js";
import CoreForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';
import ApiVertrag from "../../../api/lehrveranstaltung/vertrag.js";

export default{
	name: "LVLektorVertrag",
	components: {
		CoreFilterCmpt,
		CoreForm,
		FormInput
	},
	emits: [
		'canceledVertrag'
	],
	props: {
		lehreinheit_id: Number,
		mitarbeiter_uid: String
	},

	inject: {
		dropdowns: {
			from: 'dropdowns'
		},
		showVertragsdetails: {
			from: 'configShowVertragsdetails',
			default: false
		}
	},

	data() {
		return{
			data: null,
			internal_mitarbeiter_uid: null,
		}
	},
	watch: {
		lehreinheit_id:
		{
			deep: true,
			handler(newVal, oldVal) {
				this.data = null;
				this.internal_mitarbeiter_uid = null;
			}

		},
		mitarbeiter_uid:
		{
			deep: true,
			handler(newVal, oldVal) {
				this.internal_mitarbeiter_uid = newVal;

				if (newVal === null)
					this.data = null;
				else if (newVal !== undefined && this.lehreinheit_id !== undefined)
					this.getLektorVertrag();
			}
		},
	},
	computed: {
		vertragsstatus() {
			if (!this.data || !this.data.vertrag) return;

			const betragVertrag = Number(this.data.vertrag.betrag) || 0;
			const stundenVertrag = Number(this.data.vertrag.vertragsstunden) || 0;

			const semStunden = Number(this.data.semesterstunden) || 0;
			const stundensatz = Number(this.data.stundensatz) || 0;

			const kostenAktuell  = semStunden * stundensatz;

			return (stundenVertrag !== semStunden || betragVertrag !== kostenAktuell) ? 'Geändert' : (this.data.vertrag.vertragsstatus || '');
		},
	},
	methods: {
		getLektorVertrag ()
		{
			if (this.showVertragsdetails === false)
				return;

			if (!this.lehreinheit_id || !this.internal_mitarbeiter_uid)
				return;

			this.$api.call(ApiVertrag.getByLeEmp(this.lehreinheit_id, this.internal_mitarbeiter_uid))
				.then(result => {
					this.data = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},

		async cancelVertrag()
		{

			if (await this.$fhcAlert.confirm({
				message: this.$p.t('lehre', 'vertragConfirm'),
				acceptLabel: this.$p.t('ui', 'ja').charAt(0).toUpperCase() + this.$p.t('ui', 'ja').slice(1),
				acceptClass: 'btn btn-danger'}) === false)
				return;
			let needUpdate = {
				vertrag_id: this.data.vertrag.vertrag_id,
				mitarbeiter_uid: this.mitarbeiter_uid,
				lehreinheit_id: this.lehreinheit_id
			}
			this.$api.call(ApiVertrag.cancelByLeEmp(needUpdate))
				.then(result => {
					this.data.vertrag = null;
					this.$emit('canceledVertrag');
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
	},
	template: `
		<core-form ref="form">
			<fieldset class="overflow-hidden" v-if="showVertragsdetails">
				<legend>  {{$p.t('lehre', 'vertragsdetails')}}  
						{{ data?.vertrag === null ? ' – ' + $p.t('lehre', 'keinvertrag') : '' }}
				</legend>
				<template v-if="data?.vertrag">
					<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
						<div class="d-flex align-items-center flex-wrap gap-2">
							<span class="fw-bold">{{ $p.t('lehre', 'vertragsstatus') }}:</span>
							<span :class="{ 'fw-bold': vertragsstatus === 'Geändert' }">{{ vertragsstatus }}</span>
							<button
								type="button"
								class="btn btn-outline-secondary btn-sm"
								@click="cancelVertrag"
								:title="$p.t('lehre', 'cancelvertrag')"
							>
								<i class="fa-solid fa-ban"></i>
							</button>
						</div>
					</div>
					<div class="mb-2 fw-bold text-decoration-underline">
						{{ $p.t('lehre', 'vertragurfassung') }}
					</div>
					<div class="ps-4">
						<div class="d-flex mb-2 gap-2">
							<span class="fw-bold">{{ $p.t('lehre', 'semesterstunden') }}:</span>
							<span>{{ data.vertrag.vertragsstunden }}</span>
						</div>
						<div class="d-flex mb-2 gap-2">
							<span class="fw-bold">{{ $p.t('lehre', 'studiensemester') }}:</span>
							<span>{{ data.vertrag.vertragsstunden_studiensemester_kurzbz }}</span>
						</div>
					</div>
				</template>
			</fieldset>
		</core-form>
	`
};