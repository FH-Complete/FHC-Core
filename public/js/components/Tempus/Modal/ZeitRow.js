import FormInput from "../../Form/Input.js";
import { normalizeTime, normalizeDate } from "../../../helpers/Tempus/modalHelper.js";

export default {
	name: 'ZeitRow',
	components: {
		FormInput
	},
	props: {
		event: {
			type: Object,
			required: true,
		}
	},
	inject: {
		showRaster: {
			from: 'showRaster',
			default: false
		},
		teachingunits: {
			from: 'teachingunits',
			default: () => []
		},
		modalActions: {
			from: 'modalActions',
			default: () => ({})
		},
	},
	data() {
		const beginn = normalizeTime(this.event.beginn);
		const ende = normalizeTime(this.event.ende);

		return {
			editDatum: normalizeDate(this.event.datum),
			editBeginn: beginn,
			editEnde: ende,
			editStundeVon: this.teachingunits.find(unit => normalizeTime(unit.start) === beginn)?.id ?? null,
			editStundeBis: this.teachingunits.find(unit => normalizeTime(unit.end) === ende)?.id ?? null,
		};
	},
	computed: {
		teachingunitsBis() {
			if (this.editStundeVon === null)
				return this.teachingunits;
			return this.teachingunits.filter(unit => unit.id >= this.editStundeVon);
		}
	},
	watch: {
		editStundeVon(newVal) {
			if (this.editStundeBis === null || this.editStundeBis < newVal)
				this.editStundeBis = newVal;
		}
	},
	methods: {
		getTimeRange() {
			if (!this.editDatum) {
				this.$refs.editDatumInput.setFeedback(
					false,
					this.$p.t('ui', 'error_fieldRequired', { field: this.$p.t('global', 'datum') })
				);
				return null;
			}

			if (this.showRaster && this.editStundeVon !== null && this.editStundeBis !== null) {
				const vonUnit = this.teachingunits.find(unit => unit.id === this.editStundeVon);
				const bisUnit = this.teachingunits.find(unit => unit.id === this.editStundeBis);

				return {
					start_time: this.editDatum + ' ' + vonUnit.start,
					end_time: this.editDatum + ' ' + bisUnit.end,
				};
			}

			if (!this.editBeginn || !this.editEnde || this.editBeginn > this.editEnde)
				return null;

			return {
				start_time: this.editDatum + ' ' + this.editBeginn,
				end_time: this.editDatum + ' ' + this.editEnde,
			};
		},
		save() {
			const range = this.getTimeRange();
			if (!range)
				return;

			this.modalActions.saveEventTime({
				kalender_id: this.event.kalender_id,
				...range
			});
		}
	},
	template: /*html*/`
	<tr>
		<th>{{ $p.t('global','datum') + ':' }}</th>
		<td>
			<form-input
				ref="editDatumInput"
				type="DatePicker"
				v-model="editDatum"
				model-type="yyyy-MM-dd"
				name="editDatum"
				:enable-time-picker="false"
				text-input
				auto-apply
				format="dd.MM.yyyy"
				class="form-control form-control-sm d-inline-block w-auto"
			></form-input>
		</td>
	</tr>
	<tr>
		<th>{{ $p.t('ui','zeitraum') + ':' }}</th>
		<td>
			<template v-if="showRaster">
				<select v-model="editStundeVon" class="form-select form-select-sm w-auto d-inline-block">
					<option v-for="unit in teachingunits" :key="unit.id" :value="unit.id">
						{{ unit.id }}. Stunde ({{ unit.start }}–{{ unit.end }})
					</option>
				</select>
				–
				<select v-model="editStundeBis" class="form-select form-select-sm w-auto d-inline-block">
					<option v-for="unit in teachingunitsBis" :key="unit.id" :value="unit.id">
						{{ unit.id }}. Stunde ({{ unit.start }}–{{ unit.end }})
					</option>
				</select>
			</template>
			<template v-else>
				<div class="timepicker-row">
					<form-input
						type="DatePicker"
						v-model="editBeginn"
						time-picker
						model-type="HH:mm"
						name="editBeginn"
						text-input
						auto-apply
						format="HH:mm"
						class="form-control form-control-sm"
					/>
					<span>–</span>
					<form-input
						type="DatePicker"
						v-model="editEnde"
						time-picker
						model-type="HH:mm"
						name="editEnde"
						text-input
						auto-apply
						format="HH:mm"
						class="form-control form-control-sm"
					/>
				</div>
			</template>
		</td>
	</tr>`
};