import MitarbeiterHeader from "./MitarbeiterHeader.js";
import MitarbeiterDetails from "./MitarbeiterDetails.js";
import Vertraege from "./Vertraege.js";
import VerticalSplit from "../verticalsplit/verticalsplit.js";

export default {
	components: {
		VerticalSplit,
		MitarbeiterHeader,
		MitarbeiterDetails,
		Vertraege
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
<div>
	<div class="container-fluid overflow-hidden">
		<div class="row h-100">
			<main class="col-md-8 ms-sm-auto col-lg-9 col-xl-10">
			<!--<div class="col-md-12">-->
				<vertical-split>
					<template #top>	
						<MitarbeiterHeader :filterMa="filterMa" :vertragsarten="vertragsarten" @selectedPerson="selectPerson" />
					</template>
					<template #bottom>
						<div class="col" v-if="person_id!=null">
							<mitarbeiter-details :person_id="person_id"></mitarbeiter-details>
							<h5>Verträge</h5>
							<Vertraege :endpoint="$fhcApi.factory.vertraege.person" :person_id="this.person_id" />
						</div>
					<template>
				</vertical-split>
			</main>	
			</div>
		</div>
	</div>
	`
}