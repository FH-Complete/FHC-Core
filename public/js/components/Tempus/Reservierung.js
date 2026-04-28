import BsModal from '../Bootstrap/Modal.js';
import FormInput from '../Form/Input.js';
import ApiReservierung from '../../api/factory/tempus/reservierung.js';


export default {
	name: 'ReservierungModal',
	components: {
		BsModal,
		FormInput
	},
	props: {
		ortKurzbz: {
			type: String,
			default: null
		}
	},
	emits: ['saved'],
	data() {
		return {
			titel: '',
			beschreibung: '',
			start: null,
			end: null,
			ort_kurzbz: null,
			raeume_array: [],
			studiensemester_array: [],
			teilnehmer: [],
			specialFinalGroups: [],
			rollen_array: [],
			studiengaenge: [],
			show_all_fields: false,
			filteredUsers: [],
			filteredGroups: [],
			abortController: null
		};
	},
	created()
	{
		this.$api.call(ApiReservierung.getInformation())
			.then(result => result.data)
			.then(result => {

				if (result.berechtigt)
				{
					this.studiengaenge = result.studiengaenge
				}
				this.show_all_fields = result.berechtigt;

				this.raeume_array = result.raeume;
				this.rollen_array = result.rollen;
				this.studiensemester_array = result.studiensemester;

			})
			.catch(this.$fhcAlert.handleSystemError);

	},
	methods: {

		async searchGroup(event)
		{
			const query = event.query.trim();

			if (query.length < 2)
				return [];

			if (this.abortController)
				this.abortController.abort();

			this.abortController = new AbortController();

			this.$api.call(ApiReservierung.searchGroup(query), { signal: this.abortController.signal })
				.then(result => {
					this.filteredGroups =result.data.map(gruppe => ({
						label: gruppe.bezeichnung
							? `${gruppe.gruppe_kurzbz.trim()} (${gruppe.bezeichnung})`
							: gruppe.gruppe_kurzbz.trim(),
						gid: gruppe.gid,
						gruppe_kurzbz: gruppe.gruppe_kurzbz.trim(),
						lehrverband: gruppe.lehrverband,
					}));
				})
				.catch(this.$fhcAlert.handleSystemError);
		},

		searchUser(event)
		{
			const query = event.query.trim();

			if (!query || query.length < 2)
			{
				this.filteredUsers = [];
				return;
			}

			if (this.abortController)
				this.abortController.abort();

			this.abortController = new AbortController();

			this.$api.call(ApiReservierung.searchTeilnehmer(query), { signal: this.abortController.signal })
				.then(result => {
					this.filteredUsers = result.data.map(u => ({
						label: `${u.nachname} ${u.vorname} (${u.uid})`,
						uid: u.uid
					}));
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		selectUser(event, index)
		{
			this.teilnehmer[index].uid = event.value.uid;
			this.teilnehmer[index].label = event.value.label;
		},
		selectFinalGroup(event, index)
		{
			this.specialFinalGroups[index].gid = event.value.gid;
			this.specialFinalGroups[index].gruppe_kurzbz = event.value.gruppe_kurzbz;
			this.specialFinalGroups[index].lehrverband = event.value.lehrverband;
		},


		show(start, end)
		{
			this.titel = '';
			this.beschreibung = '';
			this.start = start;
			this.end = end;
			this.ort_kurzbz = this.ortKurzbz ?? null;
			this.teilnehmer = [{ uid: null, rolle: null }];
			this.specialFinalGroups = [{ gid: null, studiensemester_kurzbz: null, lehrverband: null, gruppe_kurzbz: null, rolle: null }];
			this.$refs.modal.show();
		},
		hide()
		{
			this.$refs.modal.hide();
		},
		save()
		{
			this.$api.call(
				ApiReservierung.addReservierung(
					this.titel,
					this.beschreibung,
					this.ort_kurzbz,
					luxon.DateTime.fromFormat(this.start, 'yyyy-MM-dd HH:mm').toISO(),
					luxon.DateTime.fromFormat(this.end, 'yyyy-MM-dd HH:mm').toISO(),
					this.teilnehmer.filter(t => t.uid && t.rolle).map(nehmer => ({ uid: nehmer.uid, rolle: nehmer.rolle })),
					this.specialFinalGroups.filter(group => group.gid && group.rolle && group.studiensemester_kurzbz).map(group => ({ gid: group.gid, lehrverband: group.lehrverband, gruppe_kurzbz: group.gruppe_kurzbz, rolle: group.rolle, studiensemester_kurzbz: group.studiensemester_kurzbz })),
				)
			).then(() => {
				this.$refs.modal.hide();
				this.$emit('saved');
			});
		},
	},
	// language=HTML
	template: `
		<bs-modal ref="modal" class="bootstrap-prompt" dialogClass="modal-xl">
			<template #title>Neue Reservierung</template>
			<template #default>
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
					<div class="col-6">
						<form-input
							type="datepicker"
							v-model="start"
							name="star_date"
							format="dd.MM.yyyy HH:mm"
							auto-apply
							:enable-time-picker="true"
							preview-format="dd.MM.yyyy HH:mm"
							model-type="yyyy-MM-dd HH:mm"
							:label="$p.t('ui', 'von')"
						/>
					</div>
					<div class="col-6">
						<form-input
							type="datepicker"
							v-model="end"
							name="end_time"
							format="dd.MM.yyyy HH:mm"
							auto-apply
							:enable-time-picker="true"
							preview-format="dd.MM.yyyy HH:mm"
							model-type="yyyy-MM-dd HH:mm"
							:label="$p.t('global', 'bis')"
						/>
					</div>
					<div class="col-6">
						<form-input
							:label="$p.t('global', 'raum')"
							type="select"
							container-class="col-6"
							v-model="ort_kurzbz"
							name="ort_kurzbz"
						>
							<option
								v-for="raum in raeume_array"
								:value="raum.ort_kurzbz"
								:key="raum.ort_kurzbz"
							>
								{{ raum.ort_kurzbz }} {{ raum.bezeichnung }}
							</option>
						</form-input>
					</div>
					
					
					<div class="col-12" v-if="show_all_fields">
						<div v-for="(nehmer, i) in teilnehmer" :key="i" class="d-flex gap-2 mb-2 align-items-end">
							<form-input
								type="autocomplete"
								:label="$p.t('ui', 'teilnehmende')"
								:suggestions="filteredUsers"
								v-model="nehmer.label"
								field="label"
								container-class="flex-grow-1"
								:name="'user_' + i"
								@complete="searchUser($event, i)"
								@item-select="selectUser($event, i)"
							></form-input>
							<form-input
								type="select"
								:label="$p.t('lehre', 'status_rolle')"
								v-model="nehmer.rolle"
								:name="'rolle_' + i"
								
							>
								<option v-for="rolle in rollen_array" :value="rolle.rolle_kurzbz" :key="rolle.rolle_kurzbz">
									{{ rolle.bezeichnung }}
								</option>
							</form-input>
							<button type="button" class="btn btn-outline-danger" @click="teilnehmer.splice(i, 1)">
								<i class="fa-solid fa-xmark"></i>
							</button>
						</div>
						<button type="button" class="btn btn-outline-secondary btn-sm" @click="teilnehmer.push({ uid: null, label: '', rolle: null })">
							<i class="fa-solid fa-plus me-1"></i>{{$capitalize( $p.t('global', 'hinzufuegen') )}}
						</button>
					</div>

					<div class="col-12" v-if="show_all_fields">
						<div v-for="(group, i) in specialFinalGroups" :key="i" class="d-flex gap-3 mb-3 align-items-end">
							<form-input
									type="autocomplete"
									:label="$p.t('lehre', 'gruppe')"
									:suggestions="filteredGroups"
									v-model="group.label"
									field="label"
									container-class="flex-grow-1"
									:name="'group_' + i"
									@complete="searchGroup($event, i)"
									@item-select="selectFinalGroup($event, i)"
							></form-input>
							<form-input
									type="select"
									:label="$p.t('lehre', 'studiensemester')"
									v-model="group.studiensemester_kurzbz"
									:name="'studiensemester_' + i"
							>
								<option v-for="studiensemester in studiensemester_array" :value="studiensemester.studiensemester_kurzbz" :key="studiensemester.studiensemester_kurzbz">
									{{ studiensemester.studiensemester_kurzbz }}
								</option>
							</form-input>
							
							<form-input
									type="select"
									:label="$p.t('lehre', 'status_rolle')"
									v-model="group.rolle"
									:name="'rolle_' + i"
							>
								<option v-for="rolle in rollen_array" :value="rolle.rolle_kurzbz" :key="rolle.rolle_kurzbz">
									{{ rolle.bezeichnung }}
								</option>
							</form-input>
							<button type="button" class="btn btn-outline-danger" @click="specialFinalGroups.splice(i, 1)">
								<i class="fa-solid fa-xmark"></i>
							</button>
						</div>
						<button type="button" class="btn btn-outline-secondary btn-sm" @click="specialFinalGroups.push({ gruppe_kurzbz: null, studiensemester_kurzbz: null, label: '', rolle: null })">
							<i class="fa-solid fa-plus me-1"></i>{{$p.t('lehre', 'gruppe')}} {{$p.t('global', 'hinzufuegen')}}
						</button>
					</div>
				</div>
			</template>
			<template #footer>
				<button type="button" class="btn btn-primary" @click="save">Speichern</button>
			</template>
		</bs-modal>`
}