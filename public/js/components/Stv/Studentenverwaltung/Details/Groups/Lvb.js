import FhcForm from "../../../../Form/Form.js";

import ApiStvLvb from '../../../../../api/factory/stv/lehrverband.js';

export default {
	name: 'TabGroupsLvb',
	components: {
		FhcForm
	},
	props: {
		students: Object
	},
	emits: [
		"submit"
	],
	data() {
		return {
			lvbList: [],
			selectedSemester: false,
			selectedVerband: false,
			selectedGruppe: false
		};
	},
	computed: {
		allAreStudents() {
			if (Array.isArray(this.students))
				return this.students.every(ps => ps.uid);
			return this.students.uid;
		},
		studiengang_kz() {
			if (Array.isArray(this.students)) {
				const first = this.students.find(Boolean);
				if (this.students.every(ps => ps.studiengang_kz === first.studiengang_kz))
					return first.studiengang_kz;
				return false;
			}
			return this.students.studiengang_kz;
		},
		semester: {
			get() {
				if (this.selectedSemester !== false) {
					if (this.lvbList.some(item => item.semester == this.selectedSemester))
						return this.selectedSemester;
					return false;
				}
				if (Array.isArray(this.students)) {
					const first = this.students.find(Boolean);
					if (this.lvbList.every(item => item.semester != first.semester))
						return false;
					if (this.students.every(ps => ps.semester === first.semester))
						return first.semester;
					return false;
				}
				if (this.lvbList.some(item => item.semester == this.students.semester))
					return this.students.semester;
				return false;
			},
			set(value) {
				this.selectedSemester = value;
			}
		},
		verband: {
			get() {
				if (this.semester === false)
					return false;
				if (this.selectedVerband !== false) {
					if (this.lvbListVerband.some(item => item.verband == this.selectedVerband))
						return this.selectedVerband;
					return false;
				}
				if (Array.isArray(this.students)) {
					const first = this.students.find(Boolean);
					if (this.lvbListVerband.every(item => item.verband != first.verband))
						return false;
					if (this.students.every(ps => ps.verband === first.verband))
						return first.verband;
					return false;
				}
				if (this.lvbListVerband.some(item => item.verband == this.students.verband))
					return this.students.verband;
				return false;
			},
			set(value) {
				this.selectedVerband = value;
			}
		},
		gruppe: {
			get() {
				if (this.verband === false)
					return false;
				if (this.selectedGruppe !== false) {
					if (this.lvbListGruppe.some(item => item.gruppe == this.selectedGruppe))
						return this.selectedGruppe;
					return false;
				}
				if (Array.isArray(this.students)) {
					const first = this.students.find(Boolean);
					if (this.lvbListGruppe.every(item => item.gruppe != first.gruppe))
						return false;
					if (this.students.every(ps => ps.gruppe === first.gruppe))
						return first.gruppe;
					return false;
				}
				if (this.lvbListGruppe.some(item => item.gruppe == this.students.gruppe))
					return this.students.gruppe;
				return false;
			},
			set(value) {
				this.selectedGruppe = value;
			}
		},
		stgSemester() {
			if (!this.lvbList.length)
				return [];

			const semester = new Set(this.lvbList.map(lvb => lvb.semester));

			return Array.from(semester).sort((a, b) => a - b);
		},
		lvbListVerband() {
			if (!this.lvbList.length)
				return [];
			if (this.semester === false)
				return [];

			return this.lvbList.filter(lvb => this.semester == lvb.semester);
		},
		semesterVerband() {
			if (!this.lvbListVerband.length)
				return [];

			const verband = new Set(this.lvbListVerband.map(lvb => lvb.verband.replace(/ /g, '')));

			return Array.from(verband).filter(Boolean).sort();
		},
		lvbListGruppe() {
			if (!this.lvbListVerband.length)
				return [];
			if (this.verband === false)
				return [];

			return this.lvbListVerband.filter(lvb => this.verband == lvb.verband);
		},
		verbandGruppe() {
			if (!this.lvbListGruppe.length)
				return [];

			const gruppe = new Set(this.lvbListGruppe.map(lvb => lvb.gruppe.replace(/ /g, '')));

			return Array.from(gruppe).filter(Boolean).sort();
		}
	},
	watch: {
		studiengang_kz() {
			this.loadGroupsForStg();
		}
	},
	methods: {
		loadGroupsForStg() {
			this.lvbList = [];

			if (this.studiengang_kz === false)
				return;

			this.$api
				.call(ApiStvLvb.getTree(this.studiengang_kz))
				.then(result => this.lvbList = result.data)
				.catch(this.$fhcAlert.handleSystemError)
		},
		onSubmit() {
			let params = {
				studiengang_kz: this.studiengang_kz,
				semester: this.semester,
				verband: this.verband,
				gruppe: this.gruppe
			};
			this.$emit("submit", params);
		}
	},
	created() {
		this.loadGroupsForStg();
	},
	template: /* html */`
	<div class="stv-details-groups-lvb">
		<fhc-form
			v-if="allAreStudents && studiengang_kz"
			ref="form"
			class="input-group"
			@submit.prevent="onSubmit"
		>
			<span class="input-group-text">
				{{ $p.t('lehre/semester') }}
			</span>
			<select
				v-model="semester"
				class="form-select"
			>
				<option v-for="semester in stgSemester">{{ semester }}</option>
			</select>
			<span
				class="input-group-text"
				:class="{'text-muted': semester === false}"
			>
				{{ $p.t('lehre/verband') }}
			</span>
			<select
				v-model="verband"
				class="form-select"
				:disabled="semester === false"
			>
				<option v-for="verband in semesterVerband">{{ verband }}</option>
			</select>
			<span
				class="input-group-text"
				:class="{'text-muted': verband === false}"
			>
				{{ $p.t('lehre/gruppe') }}
			</span>
			<select
				v-model="gruppe"
				class="form-select"
				:disabled="verband === false"
			>
				<option v-for="gruppe in verbandGruppe">{{ gruppe }}</option>
			</select>
			<button
				type="submit"
				class="btn btn-primary"
				:disabled="gruppe === false && verband === false"
			>
				{{ $p.t('ui/change') }}
			</button>
		</fhc-form>
		<div v-if="!allAreStudents" class="alert alert-danger">
			{{ $p.t('stv/groups_error_notallstudents') }}
		</div>
		<div v-if="!studiengang_kz" class="alert alert-danger">
			{{ $p.t('stv/groups_error_notsamestg') }}
		</div>
	</div>`
};