import StudiengangPerson from "./StudiengangPerson";
import StudiengangVertretung from "./StudiengangVertretung";

export default {
data(){
	return{
		studiengang:null,
		semester: null,
		stg_ltg: null,
		gf_ltg: null,
		stv_ltg: null,
		ass: null,
		hochschulvertr: null,
		stdv: null,
		jahrgangsvertr: null,
	}
},
components:{
	StudiengangPerson,
	StudiengangVertretung,
},
template:/*html*/`

		<template v-if="studiengang?.bezeichnung && semester">
			<div class="card card-body mb-3">
				<div class="mb-3">
					<h6 class="fw-bold">Studiengang:</h6>
					{{studiengang.bezeichnung}}
				</div>
				<div :class="{'mb-3':studiengang?.zusatzinfo_html}">
					<h6 class="fw-bold">Semester: </h6>
					{{semester}}
				</div>
				<div v-if="studiengang?.zusatzinfo_html" v-html="studiengang?.zusatzinfo_html"></div>
			</div>
		</template>
		<template v-for="{title, collection} in collection_array">
			<h5 class="fw-bold" v-if="Array.isArray(collection)  && collection.length !==0">{{title}}</h5>
			<template v-for="person in collection">
				<div class="mb-3">
					<studiengang-person v-bind="person"></studiengang-person>
				</div>
			</template>
		</template>
		<template v-if="hochschulvertr && Array.isArray(hochschulvertr) && hochschulvertr.length >0">
			<studiengang-vertretung showBezeichnung title="Hochschulvertretung" :vertretungsList="hochschulvertr"></studiengang-vertretung>
		</template>
		<template v-if="stdv && Array.isArray(stdv) && stdv.length >0">
			<studiengang-vertretung :title="'Studienvertretung'.concat(studiengang.kurzbzlang??'')" :vertretungsList="stdv"></studiengang-vertretung>
		</template>
		<template v-if="jahrgangsvertr && Array.isArray(jahrgangsvertr) && jahrgangsvertr.length >0">
			<studiengang-vertretung title="Jahrgangsvertretung" :vertretungsList="jahrgangsvertr"></studiengang-vertretung>
		</template>
	
`,
computed:{
	collection_array: function(){
		return [{ title: "Studiengangsleitung", collection: this.stg_ltg }, { title: "geschäftsführende Leitung", collection: this.gf_ltg },{ title: "stellvertretende Leitung", collection: this.stv_ltg },{ title: "Sekretariat", collection: this.ass} ];
	},
},
mounted(){
	this.$fhcApi.factory.studiengang.studiengangInformation()
	.then(res => res.data)
	.then(studiengangInformationen => {
		Object.assign(this, studiengangInformationen);
	});

},

};