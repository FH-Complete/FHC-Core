export default {
	props: {
		student: Object
	},
	data() {
		return {
			person_id: '',
			bpk: '',
			anrede: '',
			titelpre: '',
			titelpost: '',
			nachname: '',
			vorname: '',
			vornamen: '',
			wahlname: '',
			gebdatum: '',
			gebort: '',
			gebnation: ''
		}
	},
	watch: {
		student(n) {
			this.person_id = n.person_id;
			this.bpk = n.bpk;
			this.anrede = n.anrede;
			this.titelpre = n.titelpre;
			this.titelpost = n.titelpost;
			this.nachname = n.nachname;
			this.vorname = n.vorname;
			this.vornamen = n.vornamen;
			this.wahlname = n.wahlname;
			this.gebdatum = n.gebdatum;
			// TODO(chris): gebdatum > datepicker
			// TODO(chris): gebort & getnation?
		}
	},
	template: `
	<div class="stv-details-details h-100 pb-3">
		<fieldset>
			<legend>Person</legend>
			<div class="row mb-3">
				<label for="stv-details-person_id" class="col-sm-1 col-form-label">Person ID</label>
				<div class="col-sm-3">
					<input id="stv-details-person_id" type="text" class="form-control" v-model="person_id">
				</div>
				<label for="stv-details-bpk" class="col-sm-1 col-form-label">BPK</label>
				<div class="col-sm-3">
					<input id="stv-details-bpk" type="text" class="form-control" v-model="bpk">
				</div>
			</div>
			<div class="row mb-3">
				<label for="stv-details-anrede" class="col-sm-1 col-form-label">Anrede</label>
				<div class="col-sm-3">
					<input id="stv-details-anrede" type="text" class="form-control" v-model="anrede">
				</div>
				<label for="stv-details-titelpre" class="col-sm-1 col-form-label">Titel Pre</label>
				<div class="col-sm-3">
					<input id="stv-details-titelpre" type="text" class="form-control" v-model="titelpre">
				</div>
				<label for="stv-details-titelpost" class="col-sm-1 col-form-label">Titel Post</label>
				<div class="col-sm-3">
					<input id="stv-details-titelpost" type="text" class="form-control" v-model="titelpost">
				</div>
			</div>
			<div class="row mb-3">
				<label for="stv-details-wahlname" class="col-sm-1 col-form-label">Wahlname</label>
				<div class="col-sm-3">
					<input id="stv-details-wahlname" type="text" class="form-control" v-model="wahlname">
				</div>
			</div>
			<div class="row mb-3">
				<label for="stv-details-gebdatum" class="col-sm-1 col-form-label">Geburtsdatum</label>
				<div class="col-sm-3">
					<input id="stv-details-gebdatum" type="text" class="form-control" v-model="gebdatum">
				</div>
				<label for="stv-details-gebort" class="col-sm-1 col-form-label">Geburtsort</label>
				<div class="col-sm-3">
					<input id="stv-details-gebort" type="text" class="form-control" v-model="gebort">
				</div>
				<label for="stv-details-gebnation" class="col-sm-1 col-form-label">Geburtsnation</label>
				<div class="col-sm-3">
					<input id="stv-details-gebnation" type="text" class="form-control" v-model="gebnation">
				</div>
			</div>
		</fieldset>
	</div>`
};