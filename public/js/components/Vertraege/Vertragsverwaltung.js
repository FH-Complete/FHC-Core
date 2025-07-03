import {CoreNavigationCmpt} from "../navigation/Navigation.js";
import MitarbeiterHeader from "./MitarbeiterHeader.js";
import FhcHeader from "../DetailHeader/DetailHeader.js";
import VertraegeMitarbeiter from "./Vertraege.js";
import VerticalSplit from "../verticalsplit/verticalsplit.js";
import ApiCoreVertraege from '../../api/factory/vertraege/vertraege.js';


export default {
	name: 'Vertragsverwaltung',
	components: {
		CoreNavigationCmpt,
		VerticalSplit,
		MitarbeiterHeader,
		FhcHeader,
		VertraegeMitarbeiter
	},
	props: {
		config: Object,
		permissions: Object,
	},
	provide() {
		return {
			configDomain: this.config.domain,
			hasSchreibrechte: this.permissions['vertragsverwaltung_schreibrechte'],
		}
	},
	data() {
		return {
			person_id: null,
			endpoint: ApiCoreVertraege
		}
	},
	methods: {
		selectPerson(selected){
			this.person_id = selected;
		},
		redirectToLeitung(leitung){
			this.person_id = leitung.person_id;
		}
	},
	template: `
		<!-- Navigation component -->
		<core-navigation-cmpt></core-navigation-cmpt>

		<div class="vv">
			<main>
				<vertical-split ref="vsplit">
					<template #top>	
						<div class="d-flex flex-column" style="height: 100%;">
							<mitarbeiter-header
								:endpoint="endpoint"
								@selectedPerson="selectPerson"/>
						</div>
					</template>
					<template #bottom>
						<div class="col" v-if="person_id!=null">
							<fhc-header
								:person_id="person_id"
								typeHeader="mitarbeiter"
								@redirectToLeitung="redirectToLeitung"
							></fhc-header>
							<vertraege-mitarbeiter :endpoint="endpoint" :person_id="this.person_id"/>
						</div>
					</template>
				</vertical-split>
			</main>	
		</div>
		`
}