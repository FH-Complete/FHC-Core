import BsModal from "../Bootstrap/Modal.js";
import ApiKalender from "../../api/factory/tempus/kalender.js";

export default {
	name: "RaumauswahlModal",
	components: {
		BsModal,
		Listbox: primevue.listbox,
	},
	emits: ["saved"],
	data() {
		return {
			entries: [],
			selectedRooms: [],
			event: null,
			loading: false,
		};
	},
	methods: {
		async show(orig)
		{
			if (!orig?.kalender_id)
				return;

			this.event = orig;
			this.loading = true;
			this.entries = [];
			this.selectedRooms = [];

			this.$refs.modal.show();

			let result = await this.$api.call(ApiKalender.getRaumvorschlag(orig.kalender_id));

			let fetchedEntries = result.data ?? [];
			let currentRooms = orig.ort_kurzbz ?? [];

			for (let ort_kurzbz of currentRooms)
			{
				let exists = fetchedEntries.some((r) => r.ort_kurzbz === ort_kurzbz);

				if (!exists)
					fetchedEntries.push({ ort_kurzbz, score: null, details: [] });
			}

			this.entries = fetchedEntries;
			this.selectedRooms = this.entries.filter((room) => {return currentRooms.includes(room.ort_kurzbz)});
			this.entries = [
				...this.entries.filter((room) => currentRooms.includes(room.ort_kurzbz)),
				...this.entries.filter((room) => !currentRooms.includes(room.ort_kurzbz)),
			];

			this.loading = false;
		},
		hide() {
			this.selectedRooms = [];

			this.$refs.modal.hide();
		},
		async confirm() {
			let ort_kurzbz = this.selectedRooms.map((room) => room.ort_kurzbz);

			if (ort_kurzbz.length < 1)
			{
				await this.$api.call(
					ApiKalender.deleteOrtEntry(this.event.kalender_id),
				);
			}
			else
			{
				await this.$api.call(
					ApiKalender.updateKalenderEvent(this.event.kalender_id, {ort_kurzbz}),
				);
			}

			this.hide();
			this.$emit("saved");
		},
	},
	watch: {
		selectedRooms(newVal) {
			let sorted = this.entries.filter((room) => {
				return newVal.some((sel) => sel.ort_kurzbz === room.ort_kurzbz);
			});

			let same = sorted.length === newVal.length;

			if (same)
			{
				same = sorted.every((room, i) => {
					return room.ort_kurzbz === newVal[i].ort_kurzbz;
				});
			}

			if (!same)
				this.selectedRooms = sorted;
		},
	},
	template: `
		<bs-modal
			ref="modal"
			class="bootstrap-prompt"
			data-cy="raumauswahlModal"
		>
			<template #title>{{ $p.t('lehre','roomselection') }}</template>
			<template #default>
				<div v-if="loading" class="text-center text-muted py-4">
					<i class="fa-solid fa-spinner fa-spin me-2"></i>{{ $p.t('ui','loading') }}
				</div>
				<Listbox
					v-else
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
						<div class="d-flex justify-content-between w-100">
							<span><i class="fa-solid fa-door-open me-2"></i>{{ option.ort_kurzbz }}</span>
							<span
								class="text-muted"
								v-tooltip="{ value: (option.details || []).join('\\n'), class: 'custom-tooltip' }"
							>{{ option.score }}</span>
						</div>
					</template>
				</Listbox>
			
				<div class="d-flex justify-content-end gap-2 mt-3">
					<button type="button" class="btn btn-secondary" @click="hide">
						{{ $p.t('ui','abbrechen') }}
					</button>
					<button
						type="button"
						class="btn btn-primary"
						@click="confirm"
					>
						{{ $p.t('global','speichern') }}
					</button>
				</div>
			</template>
		</bs-modal>
	`,
};