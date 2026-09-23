import BsModal from '../../Bootstrap/Modal.js';

/**
 * The Freigabe dialog: the changed LV-Noten and the password. It emits the password; the parent
 * writes the Freigabe.
 *
 *   open()  -> 'save-freigabe' (password)
 */
export default {
	name: "BenotungstoolFreigabeDialog",
	components: {
		BsModal,
		Password: primevue.password
	},
	props: {
		// the rows with an LV-Note that changed since the last Freigabe
		lvNoten: { type: Array, default: () => [] },
		notenOptions: { type: Array, default: () => [] }
	},
	emits: ['save-freigabe'],
	data() {
		return {
			visible: false,
			password: ''
		};
	},
	computed: {
		/** The Freigabe writes the LV-Note, not the Zeugnisnote. So the dialog shows both. */
		summary() {
			return this.lvNoten.map(s => ({
				uid: s.uid,
				name: `${s.vorname} ${s.nachname}`,
				zeugnisnote: this.bezeichnungOf(s.zeugnisnote) ?? s.zeugnisnote ?? '—',
				lvNote: this.bezeichnungOf(s.lv_note) ?? s.lv_note ?? '—',
				changed: (s.zeugnisnote ?? '') != (s.lv_note ?? '')
			}));
		}
	},
	methods: {
		open() {
			this.$refs.modal.show();
		},

		submit() {
			const password = this.password;

			// the password leaves the memory with the dialog
			this.password = '';
			this.$refs.modal.hide();
			this.$emit('save-freigabe', password);
		},

		bezeichnungOf(note) {
			return this.notenOptions.find(n => n.note == note)?.bezeichnung;
		}
	},
	template: `
		<bs-modal data-cy="modal-freigabe" ref="modal" class="bootstrap-prompt" dialogClass="modal-lg" bodyClass="px-4 py-4"
			@hideBsModal="visible = false"
			@showBsModal="visible = true">
			<template v-slot:title>{{ $p.t('benotungstool/noteneingabeSpeichern') }}</template>
			<template v-slot:default>
				<div v-html="$capitalize($p.t('benotungstool/notenfreigabeHinweistextv4'))"></div>
				<div v-if="summary.length" class="mt-3">
					<div class="fw-bold mb-1">{{ $p.t('benotungstool/c4freigabeSummaryHeading', [summary.length]) }}</div>
					<div class="border rounded" style="max-height: 250px; overflow-y: auto;">
						<table class="table table-sm table-hover align-middle mb-0">
							<thead>
								<tr>
									<th class="bg-body sticky-top">{{ $capitalize($p.t('benotungstool/c4student')) }}</th>
									<th class="bg-body sticky-top">{{ $capitalize($p.t('benotungstool/c4freigabeSummaryCurrent')) }}</th>
									<th class="bg-body sticky-top"></th>
									<th class="bg-body sticky-top">{{ $capitalize($p.t('benotungstool/c4freigabeSummaryReleased')) }}</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="row in summary" :key="row.uid" :data-cy="'freigabe-row-' + row.uid">
									<td>{{ row.name }} <span class="text-muted">({{ row.uid }})</span></td>
									<td>{{ row.zeugnisnote }}</td>
									<td class="text-center"><i class="fa fa-arrow-right"></i></td>
									<td data-cy="freigabe-row-lvnote" :class="{ 'fw-bold text-success': row.changed }">{{ row.lvNote }}</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="small text-muted mt-1">{{ $p.t('benotungstool/c4freigabeSummaryLegend') }}</div>
				</div>
				<div v-else class="mt-3 text-center text-muted" data-cy="freigabe-summary-empty">{{ $p.t('benotungstool/c4freigabeSummaryEmpty') }}</div>
				<!-- The password field exists only while the dialog is open. Else the password manager
					offers its entries on every other text field, for example the date of the proposal. -->
				<div v-if="visible" class="mt-3 d-flex justify-content-center" data-cy="freigabe-password">
					<Password v-model="password" :feedback="false" showIcon="fa fa-eye" :toggleMask="true" :promptLabel="$p.t('benotungstool/passwort')"></Password>
				</div>
			</template>
			<template v-slot:footer>
				<button type="button" data-cy="freigabe-submit" class="btn btn-primary" :disabled="!summary.length" @click="submit">{{ $p.t('benotungstool/noteneingabeBestätigen') }}</button>
			</template>
		</bs-modal>
	`
};
