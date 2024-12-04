import StudiengangPerson from "./StudiengangPerson";

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
	StudiengangPerson
},
template:/*html*/`
	<template v-for="{title, collection} in collection_array">
	<h3 v-if="Array.isArray(collection)  && collection.length !==0">{{title}}</h3>
	<template v-for="person in studiengangs_person_data(collection)">
		<div class="mb-3">
			<studiengang-person :person_data="person"></studiengang-person>
		</div>
	</template>
	</template>
`,
computed:{
	collection_array: function(){
		return [{ title: "Studiengangsleitung", collection: this.stg_ltg }, { title: "geschäftsführende Leitung", collection: this.gf_ltg },{ title: "stellvertretende Leitung", collection: this.stv_ltg },{ title: "Sekretariat", collection: this.ass} ];
	},
	
	studiengangs_assistenz_array: function () {
		// early return if the reactive data is not yet loaded or not present
		if (!this.ass)
			return null;

		return this.ass.map((assistenz) => {
			return {
				fullname: this.studiengangs_person_fullname(assistenz.titelpre, assistenz.vorname, assistenz.nachname),
				telefone: this.studiengangs_person_phone(assistenz.kontakt, assistenz.telefonklappe),
				ort: assistenz.planbezeichnung ?? null,
				email: this.studiengangs_person_email(assistenz.email),
			}
		})
	},
},
methods:{
	studiengangs_person_data: function (collection) {
		// early return if the reactive data is not yet loaded or not present
		if (!collection || !Array.isArray(collection) || collection.length === 0)
			return null;

		return collection.map((item) => {
			return {
				fullname: this.studiengangs_person_fullname(item.titelpre, item.vorname, item.nachname),
				telefone: this.studiengangs_person_phone(item.kontakt, item.telefonklappe),
				ort: item.planbezeichnung ?? null,
				email: this.studiengangs_person_email(item.email),
				foto: item.foto ? 'data:image/png;base64,'.concat(item.foto) : null,
			}
		})
	},
	studiengangs_person_fullname: function(titelpre, vorname, nachname){
		if (titelpre && vorname && nachname) 
		{
			return `${titelpre} ${vorname} ${nachname}`;
		}
		else if (vorname && nachname)
		{
			return `${vorname} ${nachname}`;
		}
		else if (nachname)
		{
			return vorname;
		}
		else
		{
			return null;
		}
	},
	studiengangs_person_phone: function (telefone,telefoneklappe) {
		if(telefone && telefoneklappe)
		{
			return "tel:".concat(telefone).concat(" "+telefoneklappe);
		}
		else if(telefone)
		{
			return "tel:".concat(telefone);
		}
		else
		{
			return null;
		}
	},
	studiengangs_person_email: function (email) {
		if (email) 
		{
			return "mailto:".concat(email);
		}
		else {
			return null;
		}
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