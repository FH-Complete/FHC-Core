import FormInput from '../Form/Input.js';
import ApiReservierung from '../../api/factory/tempus/reservierung.js';
import TeilnehmerRow from './Modal/TeilnehmerRow.js';

export default {
	name: 'ReservierungForm',
	components: {
		FormInput,
		Multiselect: primevue.multiselect,
		TeilnehmerRow
	},
	data() {
		return {
			titel: '',
			beschreibung: '',
			datum: null,
			beginn: null,
			ende: null,
			selected_rooms: [],
			location: null,
			raeume_array: [],
			show_all_fields: false,
		};
	},
	created() {
		this.$api.call(ApiReservierung.getInformation())
			.then(result => result.data)
			.then(result => {
				this.show_all_fields = result.berechtigt;
				this.raeume_array = result.raeume;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	methods: {
		reset(start = null, end = null, rooms = [])
		{
			this.titel = '';
			this.beschreibung = '';
			this.datum = start ? start.slice(0, 10) : null;
			this.beginn = start ? start.slice(11, 16) : null;
			this.ende = end ? end.slice(11, 16) : null;
			this.location = null;

			let prop_orte = (rooms ?? []).map(r => r.ort_kurzbz);
			this.selected_rooms = this.raeume_array.filter(raum => prop_orte.includes(raum.ort_kurzbz));

			this.$refs.teilnehmerRow?.reset();
		},
		getData()
		{
			let teilnehmer = this.$refs.teilnehmerRow?.teilnehmer ?? [];
			let gruppen = this.$refs.teilnehmerRow?.gruppen ?? [];

			return {
				titel: this.titel,
				beschreibung: this.beschreibung,
				start: this.datum && this.beginn ? this.datum + ' ' + this.beginn : null,
				end: this.datum && this.ende ? this.datum + ' ' + this.ende : null,
				orte: {
					ort_kurzbz: this.selected_rooms.map(r => r.ort_kurzbz),
					location: this.location
				},
				teilnehmer: teilnehmer
					.filter(t => t.uid && t.rolle)
					.map(t => ({ uid: t.uid, rolle: t.rolle })),
				gruppen: gruppen
					.filter(g => g.gid && g.rolle && g.studiensemester_kurzbz)
					.map(g => ({ gid: g.gid, lehrverband: g.lehrverband, gruppe_kurzbz: g.gruppe_kurzbz, rolle: g.rolle, studiensemester_kurzbz: g.studiensemester_kurzbz })),
			};
		},
	},
	template: /*html*/`
	<div class="row g-3">
		<form-input
			label="Titel"
			type="text"
			container-class="col-12"
			name="titel"
			v-model="titel"
		></form-input>
		<form-input
			label="Beschreibung"
			type="textarea"
			container-class="col-12"
			name="beschreibung"
			v-model="beschreibung"
		></form-input>
		<div class="col-4">
			<form-input
				type="datepicker"
				v-model="datum"
				name="datum"
				format="dd.MM.yyyy"
				model-type="yyyy-MM-dd"
				:enable-time-picker="false"
				text-input
				auto-apply
				:label="$p.t('global', 'datum')"
			/>
		</div>
		<div class="col-4">
			<form-input
				type="datepicker"
				v-model="beginn"
				name="beginn"
				time-picker
				format="HH:mm"
				model-type="HH:mm"
				text-input
				auto-apply
				:label="$p.t('ui', 'von')"
			/>
		</div>
		<div class="col-4">
			<form-input
				type="datepicker"
				v-model="ende"
				name="ende"
				time-picker
				format="HH:mm"
				model-type="HH:mm"
				text-input
				auto-apply
				:label="$p.t('global', 'bis')"
			/>
		</div>
		<div class="col-6">
			<label class="form-label">{{ $p.t('global', 'raum') }}</label>
			<Multiselect
				v-model="selected_rooms"
				option-label="ort_kurzbz"
				:options="raeume_array"
				placeholder="Raum"
				display="chip"
				:hide-selected="false"
				class="w-100"
				:selectionLimit="3"
			>
				<template #option="{ option }">
					{{ option.ort_kurzbz }} {{ option.bezeichnung }}
				</template>
			</Multiselect>
		</div>
		<div class="col-6">
			<form-input
				:label="$p.t('global', 'raum')"
				type="textarea"
				name="location"
				v-model="location"
			></form-input>
		</div>
		<div class="col-12" v-if="show_all_fields">
			<teilnehmer-row ref="teilnehmerRow" />
		</div>
	</div>`
};