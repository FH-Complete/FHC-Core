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
			mitarbeiter_uid: null,
			endpoint: ApiCoreVertraege
		}
	},
	methods: {
		handleSelection(selection) {
			this.mitarbeiter_uid = selection.uid;
			this.person_id = selection.person_id;
		},
	},
	template: `
		<!-- Navigation component -->
		<core-navigation-cmpt/>

		<div id="content">
			<vertical-split ref="vsplit">
				<template #top>
					<div class="d-flex flex-column" style="height: 100%;">
						<mitarbeiter-header
							:endpoint="endpoint"
							:domain="config.domain"
							@selectionChanged="handleSelection"
							/>
					</div>
				</template>
				<template #bottom>
					<div class="col" v-if="person_id!=null">
						<fhc-header
							ref="CoreDetailsHeaderRef"
							:person_id="person_id"
							:mitarbeiter_uid="this.mitarbeiter_uid"
							typeHeader="mitarbeiter"
							:domain="config.domain"
							fotoEditable
							@redirectToLeitung="handleSelection"
						>
							<template #uid>{{mitarbeiter_uid}}</template>
							<template #titleAlphaTile>pID</template>
							<template #valueAlphaTile>{{person_id}}</template>
						</fhc-header>
						<vertraege-mitarbeiter
							ref="CoreTableVertraege"
							:endpoint="endpoint"
							:person_id="this.person_id"
							:mitarbeiter_uid="this.mitarbeiter_uid"
							/>
					</div>
				</template>
			</vertical-split>
		</div>
		`
}
