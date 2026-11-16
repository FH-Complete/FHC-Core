import BsModal from '../Bootstrap/Modal.js';
import ApiTempusIssues from '../../api/factory/tempus/issues.js';

export default {
	name: 'TempusAkzeptierenModal',
	components: {
		BsModal,
	},
	emits: ['akzeptiert'],
	data() {
		return {
			issue: null,
			termine: [],
			auswahl: 'paar',
			isSaving: false,
		};
	},
	methods: {
		show(issue) {
			this.issue = issue;
			this.termine = [];
			this.auswahl = 'paar';
			this.$refs.modal.show();

			this.$api
				.call(ApiTempusIssues.getTermineZumIssue(issue.issue_id))
				.then(result => {
					this.termine = result.data || [];
				})
				.catch(this.$fhcAlert.handleSystemError)
		},
		zeit(termin) {
			const von = luxon.DateTime.fromSQL(termin.von)?.toFormat('dd.MM.yyyy HH:mm');
			const bis = luxon.DateTime.fromSQL(termin.bis)?.toFormat('HH:mm');

			return von + " " + bis;
		},
		akzeptieren() {
			this.isSaving = true;
			const kalender_id = this.auswahl === 'einzeln' ? null : this.auswahl;

			this.$api
				.call(ApiTempusIssues.akzeptieren(this.issue.issue_id, kalender_id))
				.then(() => {
					this.$refs.modal.hide();
					this.$emit('akzeptiert');
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.isSaving = false;
				});
		},
	},
	template: `
		<bs-modal ref="modal" class="bootstrap-prompt">
			<template #title>Kollision akzeptieren</template>
			<template #default>
				<p v-if="issue" class="text-muted small mb-3">{{ issue.inhalt }}</p>
				<div class="form-check mb-2">
						<input class="form-check-input" type="radio" id="akzeptieren-kollision" value="einzeln" v-model="auswahl">
						<label class="form-check-label" for="akzeptieren-kollision">
							Nur diese Kollision
						</label>
					</div>

					<div v-for="termin in termine" :key="termin.kalender_id" class="form-check mb-2">
						<input class="form-check-input" type="radio" :id="'akzeptieren-' + termin.kalender_id" :value="termin.kalender_id" v-model="auswahl">
						<label class="form-check-label" :for="'akzeptieren-' + termin.kalender_id">
							Alle Kollisionen dieser Art vom Event
							<span class="fw-semibold">{{ termin.bezeichnung }}</span>
							<span class="text-muted d-block small">
								{{ zeit(termin) }}<template v-if="termin.orte"> · {{ termin.orte }}</template>
							</span>
						</label>
					</div>
			</template>
			<template #footer>
				<button type="button" class="btn btn-secondary" @click="$refs.modal.hide()">Abbrechen</button>
				<button type="button" class="btn btn-primary" :disabled="isSaving" @click="akzeptieren">
					<span v-if="isSaving" class="spinner-border spinner-border-sm me-1" role="status"></span>
					Akzeptieren
				</button>
			</template>
		</bs-modal>
	`
};