import FormInput from '../../Form/Input.js';
import ApiReservierung from '../../../api/factory/tempus/reservierung.js';
export default {
	name: 'TeilnehmerRow',
	components: {
		FormInput
	},
	props: {
		event: {
			type: Object,
			default: null,
		}
	},
	data() {
		return {
			teilnehmer: [],
			gruppen: [],
			rollen: [],
			gruppen_rollen: [],
			studiensemester: [],
			filteredUsers: [],
			filteredGroups: [],
			userAbortController: null,
			groupAbortController: null,
		};
	},
	created() {
		this.$api.call(ApiReservierung.getInformation())
			.then(result => {
				this.rollen = result.data.rollen ?? [];
				this.gruppen_rollen = result.data.gruppen_rollen ?? [];
				this.studiensemester = result.data.studiensemester ?? [];
			});

		if (this.event)
			this.fillFromEvent();
		else
			this.reset();
	},
	methods: {
		reset() {
			this.teilnehmer = [{ uid: null, label: '', rolle: null }];
			this.gruppen = [{ gid: null, label: '', gruppe_kurzbz: null, lehrverband: null, studiensemester_kurzbz: null, rolle: null }];
		},

		fillFromEvent() {
			let organisierende = this.event.lektor.map(lektor => ({
				uid: lektor.mitarbeiter_uid,
				label: lektor.vorname + ' ' + lektor.nachname,
				rolle: 'organisator'
			}));

			let teilnehmende = this.event.teilnehmer_person.map(person => ({
				uid: person.uid,
				label: person.vorname + ' ' + person.nachname,
				rolle: 'teilnehmer'
			}));

			this.teilnehmer = organisierende.concat(teilnehmende);
			this.gruppen = this.event.teilnehmer_gruppe.map(gruppe => ({ ...gruppe, label: gruppe.gruppe_kurzbz }));
		},

		save() {
			let teilnehmer = this.teilnehmer
				.filter(t => t.uid && t.rolle)
				.map(t => ({ uid: t.uid, rolle: t.rolle }));

			let gruppen = this.gruppen
				.filter(g => g.gid && g.rolle && g.studiensemester_kurzbz)
				.map(g => ({ gid: g.gid, lehrverband: g.lehrverband, gruppe_kurzbz: g.gruppe_kurzbz, rolle: g.rolle, studiensemester_kurzbz: g.studiensemester_kurzbz }));

			return this.$api.call(ApiReservierung.updateTeilnehmer(this.event.kalender_id, teilnehmer, gruppen));
		},

		searchUser(event) {
			const query = event.query.trim();

			if (query.length < 2) {
				this.filteredUsers = [];
				return;
			}

			this.userAbortController?.abort();
			this.userAbortController = new AbortController();

			this.$api.call(ApiReservierung.searchTeilnehmer(query), { signal: this.userAbortController.signal })
				.then(result => {
					this.filteredUsers = result.data.map(u => ({
						label: `${u.nachname} ${u.vorname} (${u.uid})`,
						uid: u.uid
					}));
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		searchGroup(event) {
			const query = event.query.trim();

			if (query.length < 2) {
				this.filteredGroups = [];
				return;
			}

			this.groupAbortController?.abort();
			this.groupAbortController = new AbortController();

			this.$api.call(ApiReservierung.searchGroup(query), { signal: this.groupAbortController.signal })
				.then(result => {
					this.filteredGroups = result.data.map(gruppe => ({
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
		selectUser(event, nehmer) {
			nehmer.uid = event.value.uid;
			nehmer.label = event.value.label;
		},
		selectGroup(event, group) {
			group.gid = event.value.gid;
			group.label = event.value.label;
			group.gruppe_kurzbz = event.value.gruppe_kurzbz;
			group.lehrverband = event.value.lehrverband;
		},

		addTeilnehmer() {
			this.teilnehmer.push({ uid: null, label: '', rolle: null });
		},
		removeTeilnehmer(index) {
			this.teilnehmer.splice(index, 1);
		},
		addGruppe() {
			this.gruppen.push({ gid: null, label: '', gruppe_kurzbz: null, lehrverband: null, studiensemester_kurzbz: null, rolle: null });
		},
		removeGruppe(index) {
			this.gruppen.splice(index, 1);
		},
	},
	template: /*html*/`
	<div>
		<div class="mb-3">
			<div v-for="(nehmer, i) in teilnehmer" :key="i" class="d-flex gap-2 mb-2 align-items-end">
				<form-input
					type="autocomplete"
					:label="$p.t('ui', 'teilnehmende')"
					:suggestions="filteredUsers"
					v-model="nehmer.label"
					field="label"
					container-class="flex-grow-1"
					:name="'user_' + i"
					@complete="searchUser"
					@item-select="selectUser($event, nehmer)"
				></form-input>
				<form-input
					type="select"
					:label="$p.t('lehre', 'status_rolle')"
					v-model="nehmer.rolle"
					:name="'rolle_' + i"
				>
					<option v-for="rolle in rollen" :value="rolle.rolle_kurzbz" :key="rolle.rolle_kurzbz">
						{{ rolle.bezeichnung }}
					</option>
				</form-input>
				<button type="button" class="btn btn-outline-danger" @click="removeTeilnehmer(i)">
					<i class="fa-solid fa-xmark"></i>
				</button>
			</div>
			<button type="button" class="btn btn-outline-secondary btn-sm" @click="addTeilnehmer">
				<i class="fa-solid fa-plus me-1"></i>{{ $capitalize($p.t('global', 'hinzufuegen')) }}
			</button>
		</div>

		<div>
			<div v-for="(group, i) in gruppen" :key="i" class="d-flex gap-3 mb-3 align-items-end">
				<form-input
					type="autocomplete"
					:label="$p.t('lehre', 'gruppe')"
					:suggestions="filteredGroups"
					v-model="group.label"
					field="label"
					container-class="flex-grow-1"
					:name="'group_' + i"
					@complete="searchGroup"
					@item-select="selectGroup($event, group)"
				></form-input>
				<form-input
					type="select"
					:label="$p.t('lehre', 'studiensemester')"
					v-model="group.studiensemester_kurzbz"
					:name="'studiensemester_' + i"
				>
					<option v-for="sem in studiensemester" :value="sem.studiensemester_kurzbz" :key="sem.studiensemester_kurzbz">
						{{ sem.studiensemester_kurzbz }}
					</option>
				</form-input>
				<form-input
					type="select"
					:label="$p.t('lehre', 'status_rolle')"
					v-model="group.rolle"
					:name="'gruppe_rolle_' + i"
				>
					<option v-for="rolle in gruppen_rollen" :value="rolle.rolle_kurzbz" :key="rolle.rolle_kurzbz">
						{{ rolle.bezeichnung }}
					</option>
				</form-input>
				<button type="button" class="btn btn-outline-danger" @click="removeGruppe(i)">
					<i class="fa-solid fa-xmark"></i>
				</button>
			</div>
			<button type="button" class="btn btn-outline-secondary btn-sm" @click="addGruppe">
				<i class="fa-solid fa-plus me-1"></i>{{ $p.t('lehre', 'gruppe') }} {{ $p.t('global', 'hinzufuegen') }}
			</button>
		</div>
	</div>`
};