import StudiengangPerson from "./StudiengangPerson.js";
import StudiengangVertretung from "./StudiengangVertretung.js";

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
props:{
	displayWidget:{
		type:Boolean,
		default:false,
	}
},
components:{
	StudiengangPerson,
	StudiengangVertretung,
},
template:/*html*/`

		<template v-if="studiengang?.bezeichnung && semester">
			<div class="card card-body mb-3">
				<div class="mb-1">
					<h2 class="mb-1 pb-0">{{$p.t('lehre','studiengang')}}:</h2>
					<span class="mb-1">{{studiengang?.bezeichnung}}</span>
				</div>
				<div class="mb-1">
					<h2 class="mb-1 pb-0">Moodle:</h2>
					<a class="mb-1" target="_blank" :href="moodleLink">{{studiengang?.kurzbzlang}}</a>
				</div>
				<div :class="{'mb-1':studiengang?.zusatzinfo_html}">
					<h2 class="mb-1 pb-0">{{$p.t('lehre','studiensemester')}}: </h2>
					<span class="mb-1">{{semester}}</span>
				</div>
				<div v-if="studiengang?.zusatzinfo_html" v-html="studiengang?.zusatzinfo_html"></div>
			</div>
		</template>
		<template v-for="{title, collection} in collection_array">
			<template v-if="Array.isArray(collection)  && collection.length !==0">
				<h2 class="text-truncate">{{title}}</h2>
				<template v-if="displayWidget">
					<div class="d-flex flex-wrap flex-row mb-3 gap-2">
						<template v-for="person in collection">
							<studiengang-person displayWidget v-bind="person"></studiengang-person>
						</template>
					</div>
				</template>
				<template v-else>
					<template v-for="person in collection">
						<div class="mb-3">
							<studiengang-person v-bind="person"></studiengang-person>
						</div>
					</template>
				</template>
			</template>
		</template>
		<template v-if="hochschulvertr && Array.isArray(hochschulvertr) && hochschulvertr.length >0">
			<studiengang-vertretung showBezeichnung :title="$p.t('studiengangInformation', 'Hochschulvertretung')" :vertretungsList="hochschulvertr"></studiengang-vertretung>
		</template>
		<template v-if="stdv && Array.isArray(stdv) && stdv.length >0">
			<studiengang-vertretung :title="$p.t('studiengangInformation', 'Studienvertretung').concat(studiengang.kurzbzlang??'')" :vertretungsList="stdv"></studiengang-vertretung>
		</template>
		<template v-if="jahrgangsvertr && Array.isArray(jahrgangsvertr) && jahrgangsvertr.length >0">
			<studiengang-vertretung :title="$p.t('studiengangInformation', 'Jahrgangsvertretung')" :vertretungsList="jahrgangsvertr"></studiengang-vertretung>
		</template>
	
`,
computed:{
	// this function concatenates the studiengangsleitung and the assistenz or the 
	// geschaeftsfuehrende-Stellvertretende Leitung if both collections only contain one person
	collection_array: function(){
		let returnData = [];

		if (Array.isArray(this.stg_ltg) && this.stg_ltg.length == 1 && Array.isArray(this.ass) && this.ass.length == 1)
		{
			returnData.push({ title: `${this.$p.t('global', 'studiengangsleitung')}/${this.$p.t('studiengangInformation', 'assistenz')}` , collection: [...this.stg_ltg, ...this.ass] });
		}
		else
		{
			returnData.push({ title: this.$p.t('global', 'studiengangsleitung'), collection: this.stg_ltg });
			returnData.push({ title: this.$p.t('studiengangInformation', 'assistenz'), collection: this.ass });
		}
		if (Array.isArray(this.gf_ltg) && this.gf_ltg.length == 1 && Array.isArray(this.stv_ltg) && this.stv_ltg.length == 1)
		{
			returnData.push({ title: this.$p.t('studiengangInformation', 'geschaeftsfuehrende_stellvertretende_leitung'), collection: [...this.gf_ltg, ...this.stv_ltg] });
		}
		else
		{
			returnData.push({ title: this.$p.t('studiengangInformation', 'geschaeftsfuehrende_leitung'), collection: this.gf_ltg });
			returnData.push({ title: this.$p.t('studiengangInformation', 'stellvertretende_leitung'), collection: this.stv_ltg });
		}

		return returnData;
	},
	moodleLink: function(){
		// early return if the studiengang information is not available
		if(!this.studiengang || !this.studiengang.studiengang_kz) return;

		return `https://moodle.technikum-wien.at/course/view.php?idnumber=dl` + this.studiengang.studiengang_kz;
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