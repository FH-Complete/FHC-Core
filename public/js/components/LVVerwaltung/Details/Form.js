import CoreForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';

export default {
	name: "LVDetailsForm",
	components: {
		FormInput,
		CoreForm
	},
	props: {
		data: {
			type: Object,
			required: true
		}
	},
	inject: {
		dropdowns: {
			from: 'dropdowns'
		},
		showLVID: {
			from: 'permissionLehrveranstaltung',
			default: false
		},
		showGewichtung: {
			from: 'configShowGewichtung',
			default: true
		}
	},
	computed: {
		formattedAnmerkung: {
			get() {
				return (this.data.anmerkung || '').replace(/\\n/g, '\n');
			},
			set(value) {
				this.data.anmerkung = (value || '').replace(/\n/g, '\\n');
			}
		}
	},
	template: `
	<div>
		<div class="row align-items-start mb-3">
			<form-input
				v-if="showLVID"
				:label="$p.t('lehre', 'lehrveranstaltung_id')"
				type="text"
				container-class="col-3"
				v-model="data.lehrveranstaltung_id"
				name="lehrveranstaltung_id"
			 />
			<form-input
				v-if="showGewichtung"
				:label="$p.t('lehre', 'gewicht')"
				type="text"
				container-class="col-3"
				v-model="data.gewicht"
				name="gewicht"
			/>
			<form-input
				:label="$p.t('lehre', 'detailanmerkung')"
				type="textarea"
				container-class="col-3"
				v-model="formattedAnmerkung"
				name="anmerkung"
				id="anmerkung"
				rows="4"
			/>
		</div>
		<div class="row mb-3">
			<form-input
				:label="$p.t('lehre', 'lehrfach')"
				type="select"
				container-class="col-3"
				v-model="data.lehrfach_id"
				name="lehrfach_id"
			>
				<option
					v-for="lehrfach in data.lehrfaecher"
					:value="lehrfach.lehrveranstaltung_id"
					:key="lehrfach.lehrfach"
				>
					{{ lehrfach.lehrfach }}
				</option>
			</form-input>
			<form-input
				:label="$p.t('lehre', 'lehrform')"
				type="select"
				container-class="col-3"
				v-model="data.lehrform_kurzbz"
				name="lehrform_kurzbz"
			>
				<option
				v-for="lehrform in dropdowns.lehrform_array"
				:value="lehrform.lehrform_kurzbz"
				:key="lehrform.lehrform_kurzbz"
				>
				{{ lehrform.bez_kurz }} {{ lehrform.bez }}
				</option>
			</form-input>
		</div>
	
		<div class="row mb-3">
			<form-input
				:label="$p.t('global', 'sprache')"
				type="select"
				container-class="col-3"
				v-model="data.sprache"
				name="sprache"
			>
				<option
				v-for="sprache in dropdowns.sprachen_array"
				:key="sprache.sprache"
				:value="sprache.sprache"
				>
				{{ sprache.sprache }}
				</option>
			</form-input>
			<form-input
				:label="$p.t('lehre', 'unr')"
				type="text"
				container-class="col-3"
				v-model="data.unr"
				name="unr"
			/>
		</div>
	
		<div class="row mb-3">
			<form-input
				:label="$p.t('lehre', 'studiensemester')"
				type="select"
				container-class="col-3"
				v-model="data.studiensemester_kurzbz"
				name="studiensemester_kurzbz"
			>
				<option
				v-for="semester in dropdowns.studiensemester_array"
				:key="semester.studiensemester_kurzbz"
				:value="semester.studiensemester_kurzbz"
				>
				{{ semester.studiensemester_kurzbz }}
				</option>
			</form-input>
			<form-input
				:label="$p.t('lehre', 'lehre')"
				type="checkbox"
				container-class="col-3"
				v-model="data.lehre"
				name="lehre"
			/>
		</div>
	
		<div class="row mb-3">
			<form-input
				:label="$p.t('lehre', 'raumtyp')"
				type="select"
				container-class="col-3"
				v-model="data.raumtyp"
				name="raumtyp"
			>
				<option
				v-for="raumtyp in dropdowns.raumtyp_array"
				:value="raumtyp.raumtyp_kurzbz"
				:key="raumtyp.raumtyp_kurzbz"
				>
				{{ raumtyp.raumtyp_kurzbz }} {{ raumtyp.beschreibung }}
				</option>
			</form-input>
			<form-input
				:label="$p.t('lehre', 'raumtypalternativ')"
				type="select"
				container-class="col-3"
				v-model="data.raumtypalternativ"
				name="raumtypalternativ"
			>
				<option
				v-for="raumtyp in dropdowns.raumtyp_array"
				:value="raumtyp.raumtyp_kurzbz"
				:key="raumtyp.raumtyp_kurzbz + '-alt'"
				>
				{{ raumtyp.raumtyp_kurzbz }} {{ raumtyp.beschreibung }}
				</option>
			</form-input>
		</div>
	
		<div class="row mb-3">
			<form-input
				:label="$p.t('lehre', 'startkw')"
				type="number"
				container-class="col-2"
				v-model="data.start_kw"
				name="start_kw"
			/>
			<form-input
				:label="$p.t('lehre', 'stundenblockung')"
				type="number"
				container-class="col-2"
				v-model="data.stundenblockung"
				name="stundenblockung"
			/>
			<form-input
				:label="$p.t('lehre', 'wochenrhythmus')"
				type="number"
				container-class="col-2"
				v-model="data.wochenrythmus"
				name="wochenrythmus"
			/>
		</div>
	</div>
`
};