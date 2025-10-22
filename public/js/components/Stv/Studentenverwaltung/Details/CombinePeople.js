export default {
	name: "TabCombinePeople",
	inject: {
		cisRoot: {
			from: 'cisRoot'
		},
	},
	props: {
		modelValue: Object,
	},
	computed: {
		personIds(){
			if (this.modelValue.person_id) {
				return [this.modelValue.person_id];
			}
			return this.modelValue.map(e => e.person_id);
		}
	},
	methods: {
		combinePeople(){

			let person1_id = this.personIds[0];
			let person2_id = this.personIds[1];

			if(person1_id == person2_id) {
				return this.$fhcAlert.alertError("gleiche Person, keine Zusammenlegeung möglich");
			}

			let linkCombinePeople = this.cisRoot + 'vilesci/stammdaten/personen_wartung.php?person_id_1=' + person1_id + '&person_id_2='+ person2_id;

			console.log(linkCombinePeople);
			window.open(linkCombinePeople, '_blank');


		}
	},
	data(){
		return {}
	},
	template:  /*html*/ `
		<div class="stv-details-combine-people h-100 pb-3">
			<h4>Personen zusammenlegen</h4>

			{{personIds}}

			<div v-if="this.modelValue.length">

				<p v-if="this.modelValue.length == 2">
					<button class="btn btn-primary" @click="combinePeople"> Zusammenlegen</button>
				</p>
				<p v-else">
					ungültige Anzahl: {{this.modelValue.length}}
				</p>
			</div>
		</div>
	`
	};