import MitarbeiterHeader from "./MitarbeiterHeader.js";
import MitarbeiterDetails from "./MitarbeiterDetails.js";
import VertraegeMitarbeiter from "./Vertraege.js";
import VerticalSplit from "../verticalsplit/verticalsplit.js";

export default {
	components: {
		VerticalSplit,
		MitarbeiterHeader,
		MitarbeiterDetails,
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
		//TODO(Manu) props for filter: actually not necessary
		return {
			person_id: null,
/*			filterMa: {
				active: true,
				hasVertraege: true
			},*/
/*			vertragsarten:
				[ 'echterdv', 'externerlehrender', 'gastlektor']*/
		}
	},
	methods: {
		selectPerson(selected){
			this.person_id = selected;
		}
	},
	template: `
<div class="vv">
	<div class="container-fluid overflow-hidden">
			<main class="col-md-8 ms-sm-auto col-lg-9 col-xl-10">
				<vertical-split ref="vsplit">
					<template #top>	
	<!--					<div class="d-flex flex-column" style="height: 100%;">-->
							<MitarbeiterHeader :filterMa="filterMa" :vertragsarten="vertragsarten" @selectedPerson="selectPerson" />
<!--						</div>-->
					</template>
					<template #bottom>
						<div class="col" v-if="person_id!=null">
							<mitarbeiter-details :person_id="person_id"></mitarbeiter-details>
							<VertraegeMitarbeiter :endpoint="$fhcApi.factory.vertraege.person" :person_id="this.person_id"/>
						</div>
					<template>
				</vertical-split>
			</main>	
		</div>
	</div>
	`
}