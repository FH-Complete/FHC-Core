/**
 * Renders one person the way the teambox blocks do today: name, function, phone, room,
 * mail, and an optional photo.
 *
 * It fetches nothing. person-block and oe-personen both fill it, so one person reads the
 * same whether the editor named a UID or an organisation unit.
 */
export default {
	name: 'PersonBlock',
	props: {
		uid: String,
		name: String,
		funktion: String,
		telefon: String,
		email: String,
		ort: String,
		foto: String
	},
	computed: {
		// tel: takes digits and a leading plus.
		telefonLink() {
			return this.telefon ? 'tel:' + this.telefon.replace(/[^\d+]/g, '') : null;
		},
		fotoQuelle() {
			return this.foto ? 'data:image/png;base64,' + this.foto : null;
		}
	},
	template: /*html*/ `
		<div class="fhc-contentcomponent-person mb-3">
			<img
				v-if="fotoQuelle"
				:src="fotoQuelle"
				alt=""
				class="mb-2"
				style="width: 110px; height: auto; object-fit: scale-down;"
			>
			<div>
				<router-link v-if="uid" :to="{ name: 'ProfilView', params: { uid: uid } }">
					{{ name }}
				</router-link>
				<span v-else>{{ name }}</span>
			</div>
			<div v-if="funktion">{{ funktion }}</div>
			<div v-if="telefon">
				T: <a :href="telefonLink">{{ telefon }}</a>
			</div>
			<div v-if="ort">R: {{ ort }}</div>
			<div v-if="email">
				E: <a :href="'mailto:' + email">{{ email }}</a>
			</div>
		</div>
	`
};
