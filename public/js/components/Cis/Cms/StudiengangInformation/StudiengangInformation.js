import StudiengangPerson from "./StudiengangPerson.js";
import StudiengangVertretung from "./StudiengangVertretung.js";

import ApiStudiengang from '../../../../api/factory/studiengang.js';

export default {
data(){
	return{
		studiengang:null,
		stg_semester: null,
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
	},
	// without studiengang_kz the Studiengang of the logged in Student is loaded
	studiengang_kz:[String, Number],
	semester:[String, Number],
	// compact drops the Studiengang header and the photos, so the block fits a narrow column
	compact:{
		type:Boolean,
		default:false,
	},
},
components:{
	StudiengangPerson,
	StudiengangVertretung,
},
template:/*html*/`
		<div id="fhc-studiengang-informationen">
			<template v-if="studiengang?.bezeichnung && !compact">
				<div class="card card-body mb-3 border-0">
					<div class="mb-1">
						<h2 class="h4 mb-1 pb-0">{{$p.t('lehre','studiengang')}}:</h2>
						<span class="mb-1">{{studiengang?.bezeichnung}}</span>
					</div>
					<div class="mb-1">
						<h2 class="h4 mb-1 pb-0">Moodle:</h2>
						<a class="fhc-link-color mb-1" target="_blank" :href="moodleLink">{{studiengang?.kurzbzlang}}</a>
					</div>
					<div :class="{'mb-1':studiengang?.zusatzinfo_html}" v-if="stg_semester">
						<h2 class="h4 mb-1 pb-0">{{$p.t('lehre','studiensemester')}}: </h2>
						<span class="mb-1">{{stg_semester}}</span>
					</div>
					<div class="zusatzinfo" v-if="studiengang?.zusatzinfo_html" v-html="studiengang?.zusatzinfo_html"></div>
				</div>
			</template>
			<template v-for="{title, collection} in collection_array">
				<template v-if="Array.isArray(collection)  && collection.length !==0">
					<h2 :class="compact ? 'h6 mb-1 text-break' : 'h5 text-truncate'">{{title}}</h2>
					<template v-if="compact">
						<template v-for="person in collection">
							<studiengang-person compact v-bind="person"></studiengang-person>
						</template>
					</template>
					<template v-else-if="displayWidget">
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
				<studiengang-vertretung showBezeichnung :compact="compact" :title="$p.t('studiengangInformation', 'Hochschulvertretung')" :vertretungsList="hochschulvertr"></studiengang-vertretung>
			</template>
			<template v-if="stdv && Array.isArray(stdv) && stdv.length >0">
				<studiengang-vertretung :compact="compact" :title="$p.t('studiengangInformation', 'Studienvertretung').concat(studiengang?.kurzbzlang??'')" :vertretungsList="stdv"></studiengang-vertretung>
			</template>
			<template v-if="jahrgangsvertr && Array.isArray(jahrgangsvertr) && jahrgangsvertr.length >0">
				<studiengang-vertretung :compact="compact" :title="$p.t('studiengangInformation', 'Jahrgangsvertretung')" :vertretungsList="jahrgangsvertr"></studiengang-vertretung>
			</template>
		</div>
	
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
	infoParameter: function(){
		return [this.studiengang_kz, this.semester].join('-');
	},
	moodleLink: function(){
		// early return if the studiengang information is not available
		if(!this.studiengang || !this.studiengang.studiengang_kz) return;

		return `https://moodle.technikum-wien.at/course/view.php?idnumber=dl` + this.studiengang.studiengang_kz;
	},
},
methods:{
	loadStudiengangInformation: function(){
		this.$api
			.call(ApiStudiengang.studiengangInformation(this.studiengang_kz, this.semester))
			.then(res => res.data)
			.then(studiengangInformationen => {
				const { semester, ...rest } = studiengangInformationen ?? {};
				this.stg_semester = semester ?? null;
				Object.assign(this, rest);
			});
	},
},
watch:{
	// one watcher for both parameters prevents duplicate requests
	infoParameter: function(){
		this.loadStudiengangInformation();
	},
},
mounted() {
	this.loadStudiengangInformation();
}
};
