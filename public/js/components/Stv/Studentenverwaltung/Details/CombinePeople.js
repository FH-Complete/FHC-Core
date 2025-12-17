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
	data(){
		return {
			iframeUrl: null,
			viewLoaded: false
		}
	},
	computed: {
		personIds() {
			return Array.isArray(this.modelValue)
				? this.modelValue.map(e => e.person_id)
				: [this.modelValue.person_id];
		},
		detailStringPerson1(){
			let person1 = this.modelValue[0];
			return person1.vorname + " " + person1.nachname + "(" + person1.person_id + ")";
		},
		detailStringPerson2(){
			let person2 = this.modelValue[1];
			return person2.vorname + " " + person2.nachname + "(" + person2.person_id+ ")";
		},

	},
	methods: {
		combinePeople(){
			this.viewLoaded = true;
			let person1_id = this.personIds[0];
			let person2_id = this.personIds[1];

			if(person1_id == person2_id) {
				return this.$fhcAlert.alertError(this.$p.t('stv', 'error_combinePeople_samePerson'));
			}

			let linkCombinePeople = this.cisRoot + 'vilesci/stammdaten/personen_wartung.php?person_id_1=' + person1_id + '&person_id_2='+ person2_id;
			this.openLink(linkCombinePeople);
		},
		openLink(url) {
			this.iframeUrl = url;
		},
		goBack(){
			this.viewLoaded = false;
			this.iframeUrl = null;
		}
	},
	template:  /*html*/ `
		<div class="stv-details-combine-people h-100 pb-3">

			<div v-if="!this.viewLoaded">
				<h4>Personen zusammenlegen</h4>
				<div v-if="this.modelValue.length">
					<div v-if="this.modelValue.length == 2">
						<p>{{$p.t('stv', 'question_combine_people', { person1: detailStringPerson1, person2: detailStringPerson2 })}}</p>
						<button class="btn btn-primary" @click="combinePeople">{{$p.t('ui', 'ok')}}</button>
					</div>
					<div v-else>
						 ungültige Anzahl: {{this.modelValue.length}} <!-- should not be seen anymore-->
					</div>
				</div>
			</div>
			<div v-else>
				<button class="btn btn-secondary" @click="goBack">{{$p.t('ui', 'cancel')}}</button>
			</div>
	
			<!-- Iframe-Section -->
			<iframe
			  v-if="iframeUrl"
			  :src="iframeUrl"
			  class="w-100 mt-4 border-0"
			  style="height: 600px;"
			></iframe>

		</div>
	`
	};