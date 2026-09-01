import ApiKalender from '../../api/factory/tempus/kalender.js';
import BsModal from '../../../js/components/Bootstrap/Modal.js';
export default {
	components: {
		BsModal,
	},
	props: {
		show: { type: Boolean, default: false },
		lehreinheitId: { type: [String, Number], default: null },
		ortKurzbz: { type: String, default: null },
		startTime: { type: String, default: null },
		endTime: { type: String, default: null },
	},
	emits: ['close', 'confirmed'],
	data() {
		return {
			loading: false,
			plan: [],
			skippedWeeks: [],
			errors: [],
			restOffen: 0,
		};
	},
	watch: {
		show(val) {
			if (val) {
				this.loadPlan();
				this.$refs.bsModal.show();
			}
			else {
				this.$refs.bsModal.hide();
			}
		},
	},
	computed: {
		countTermin() {
			return (this.plan.filter(termin => termin.planen).length);
		},
		formattedSkippedWeeks() {
			return this.skippedWeeks.map(week => luxon.DateTime.fromISO(week).toFormat('dd.MM.yyyy')).join('\n');
		}
	},
	methods: {
		async loadPlan() {
			this.loading = true;
			this.plan = [];
			this.errors = [];
			this.skippedWeeks = [];
			this.restOffen = 0;

			await this.$api.call(
				ApiKalender.calculateMultiWeekPlan(
					this.lehreinheitId,
					this.ortKurzbz,
					this.startTime,
					this.endTime
				))
				.then(result => result.data)
				.then(result => {
					this.plan = (result.plan || []).map(termin => ({
						...termin,
						selected_ort_kurzbz: (
							termin.raum_vorschlaege?.find(termin => termin.ort_kurzbz === this.ortKurzbz)?.ort_kurzbz
							?? termin.raum_vorschlaege?.[0]?.ort_kurzbz
							?? termin.ort_kurzbz
						),
						planen: (termin.collisions && termin.collisions.length > 0) ? false : true,
					}));

					this.skippedWeeks = result.skipped_weeks || [];
					this.errors = result.errors || [];
					this.restOffen = result.rest_offen || 0;
					this.loading = false;
				})
				.catch(error => {
					this.$fhcAlert.handleSystemError(error);
					this.close();
				});
		},

		async confirm() {
			this.loading = true;

			await this.$api.call(
				ApiKalender.confirmMultiWeekPlan(this.plan.filter(termin => termin.planen), this.lehreinheitId)
			);

			this.$emit('confirmed');
			this.close();
		},

		close() {
			this.loading = false;
			this.$emit('close');
		},
		hasCollision(termin) {
			return termin.collisions && termin.collisions.length > 0;
		},
		roomOptions(termin) {
			return termin.raum_vorschlaege || [];
		},
		formatDate(date)
		{
			return luxon.DateTime.fromISO(date).toFormat('dd.MM.yyyy');
		},
		formatTime(time)
		{
			return luxon.DateTime.fromISO(time).toFormat('HH:mm');
		}

	},
	template: `
	<bs-modal
		ref="bsModal"
		class="bootstrap-prompt"
		dialog-class="modal-xl"
		@hidden-bs-modal="close"
	>
		<template v-slot:title>Multi-Week Verplanung Vorschau</template>

		<div v-if="loading" class="text-center py-4">
			<i class="fa fa-spinner fa-spin"></i> Wird berechnet...
		</div>

		<div v-else>
			<div v-if="restOffen > 0" class="alert alert-warning small">
				Achtung: {{ restOffen }} Stunden konnten nicht verplant werden (Semesterende erreicht).
			</div>

			<div v-if="skippedWeeks.length" class="alert alert-warning small">
				Übergesprungene Ferienwochen: <div style="white-space: pre-line;">{{ formattedSkippedWeeks  }}</div>
			</div>

			<div v-if="errors.length" class="alert alert-danger small">
				<div v-for="(err, i) in errors" :key="i">{{ err.message }}</div>
			</div>

			<table class="table table-sm align-middle">
				<thead>
					<tr>
						<th>Datum</th>
						<th>Zeit</th>
						<th>Collisions</th>
						<th>Raum</th>
						<th>Planen?</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(termin, i) in plan" :key="i" :class="hasCollision(termin) ? 'table-danger' : 'table-success'">
						<td>{{ formatDate(termin.datum) }}</td>
						<td>{{ formatTime(termin.start.split(' ')[1]) }} - {{ formatTime(termin.ende.split(' ')[1]) }}</td>
						<td>
							<span v-if="hasCollision(termin)" class="text-danger small">
								<div style="white-space: pre-line;">{{ termin.collisions.map(c => c.message).join('\\n') }}</div>
							</span>
							<span v-else class="text-success small">
								---
							</span>
						</td>
						<td>
							<select
								v-if="roomOptions(termin).length"
								class="form-select form-select-sm"
								v-model="termin.selected_ort_kurzbz"
							>
								<option
									v-for="raum in roomOptions(termin)"
									:key="raum.ort_kurzbz"
									:value="raum.ort_kurzbz"
								>
									{{ raum.ort_kurzbz }} (Score: {{ raum.score }})
								</option>
							</select>
							<span v-else class="text-muted small">Kein Raum verfügbar</span>
						</td>
						<td>
							<div class="form-check">
								<input
									type="checkbox"
									class="form-check-input"
									v-model="termin.planen"
									:id="'override-' + i"
								>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<template #footer>
			<button class="btn btn-outline-secondary" @click="close">Abbrechen</button>
			<button class="btn btn-primary" :disabled="loading || !plan.length" @click="confirm">
				{{ countTermin }} Termine anlegen
			</button>
		</template>
	</bs-modal>
	`
}