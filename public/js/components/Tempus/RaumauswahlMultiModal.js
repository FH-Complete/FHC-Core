import BsModal from "../Bootstrap/Modal.js";
import ApiKalender from "../../api/factory/tempus/kalender.js";

export default {
	name: "RaumauswahlMultiModal",
	components: {
		BsModal,
		Listbox: primevue.listbox,
	},
	emits: ["saved"],
	data() {
		return {
			terminList: [],
			entries: [],
			selectedRooms: [],
			loading: false,
		};
	},
	methods: {
		async show(origList) {
			let validList = (origList ?? []).filter((orig) => orig?.kalender_id);
			if (!validList.length) return;

			this.loading = true;
			this.terminList = validList;
			this.selectedRooms = [];
			this.$refs.modal.show();

			let result = await this.$api.call(ApiKalender.getRaeume());
			this.entries = result.data ?? [];

			this.loading = false;
		},

		hide() {
			this.terminList = [];
			this.selectedRooms = [];
			this.$refs.modal.hide();
		},

		async confirm() {
			this.loading = true;

			let ort_kurzbz = this.selectedRooms.map((room) => room.ort_kurzbz);

			await Promise.allSettled(
				this.terminList.map((termin) => {
					if (ort_kurzbz.length < 1) {
						return this.$api.call(ApiKalender.deleteOrtEntry(termin.kalender_id));
					}
					return this.$api.call(
						ApiKalender.updateKalenderEvent(termin.kalender_id, { orte: { ort_kurzbz } })
					);
				})
			);

			this.loading = false;
			this.hide();
			this.$emit("saved");
		},
	},
	template: `
		<bs-modal 
			ref="modal"
			class="bootstrap-prompt"
			data-cy="raumauswahlMultiModal"
		>
			<template #title>{{ $p.t('lehre','roomselection') }}</template>
			<template #default>
				<div v-if="loading" class="text-center text-muted py-4">
					<i class="fa-solid fa-spinner fa-spin me-2"></i>{{ $p.t('ui','loading') }}
				</div>
				
				<div v-else>
					<div class="text-muted small mb-2">
						{{ terminList.length }} {{ $p.t('global','termine') }}
					</div>
					<Listbox
						v-model="selectedRooms"
						:options="entries"
						option-label="ort_kurzbz"
						multiple
						filter
						:checkmark="true"
						:highlightOnSelect="false"
						class="w-100"
						listStyle="max-height: 300px"
						:emptyMessage="$p.t('ui', 'keineEintraegeGefunden')"
					>
					<template #option="{ option }">
						<div class="room-option">
							<i class="fa-solid fa-door-open text-muted"></i>
							<span v-if="option.raumtyp_kurzbz">
							[{{ option.raumtyp_kurzbz }}]
							</span>
							<span class="fw-semibold">
								{{ option.ort_kurzbz }}
							</span>
							<span class="text-muted small text-nowrap">
								{{ option.max_person }} Pers.
							</span>
						</div>
					</template>
				</Listbox>
				</div>
				

				<div class="d-flex justify-content-end gap-2 mt-3">
					<button type="button" class="btn btn-secondary" @click="hide">{{ $p.t('ui','abbrechen') }}</button>
					<button type="button" class="btn btn-primary" @click="confirm">{{ $p.t('global','speichern') }}</button>
				</div>
			</template>
		</bs-modal>
	`,
};