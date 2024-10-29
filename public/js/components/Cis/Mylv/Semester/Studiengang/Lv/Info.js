

const infos = {};

export default {
	props:{
		studien_semester: String,
		lehrveranstaltung_id: Number,
	},
	data: () => ({
		bezeichnung: null,
		studiengang_kuerzel: null,
		semester: null,
		orgform_kurzbz: null,
		sprache: null,
		ects: null,
		incoming: null,
		result: true,
		info: null,
	}),
	computed: {
		lektorNames() {
			return this.info.lektoren.map(e => ((e.titelpre || '') + ' ' + (e.vorname || '') + ' ' + (e.nachname || '') + ' ' + (e.titelpost || '')).trim());
		},
		lvLeitung() {
			return this.info.lvLeitung && this.info.lvLeitung.length ? this.info.lvLeitung.map(e => ((e.titelpre || '') + ' ' + (e.vorname || '') + ' ' + (e.nachname || '') + ' ' + (e.titelpost || '')).trim()) : null;
		},
		oe() {
			return this.info.oe.organisationseinheittyp ? (this.info.oe.organisationseinheittyp + ' ' + this.info.oe.bezeichnung) : '';
		},
		oeLeitung() {
			if (!this.info.oeLeitung || !this.info.oeLeitung.length)
				return ['-'];
			return this.info.oeLeitung.map(e => ((e.titelpre || '') + ' ' + (e.vorname || '') + ' ' + (e.nachname || '') + ' ' + (e.titelpost || '')).trim());
		},
		koordinator() {
			if (!this.info.koordinator || !this.info.koordinator.length)
				return null;
			return this.info.koordinator.map(e => ((e.titelpre || '') + ' ' + (e.vorname || '') + ' ' + (e.nachname || '') + ' ' + (e.titelpost || '')).trim());
		},
		currentLang() {
			if (!this.info)
				return null;
			if (this.info.lastLang)
				return this.info.lastLang;
			if (!this.info.lvinfo)
				return null;
			return this.info.lvinfoDefaultLang && this.info.lvinfo[this.info.lvinfoDefaultLang] ? this.info.lvinfoDefaultLang : Object.keys(this.info.lvinfo).shift();
		}
	},
	created() {
		this.$fhcApi.factory.lehre.getLvInfo(this.studien_semester, this.lehrveranstaltung_id)
		.then(
			res => res.data
		).then(data =>{
			Object.assign(this, 
				{
					bezeichnung : data.bezeichnung,
					studiengang_kuerzel: data.studiengang_kuerzel,
					semester: data.semester,
					orgform_kurzbz: data.orgform_kurzbz,
					sprache: data.sprache,
					ects: data.ects,
					incoming: data.incoming ?? '-',
				});
		})

		if (infos[this.lehrveranstaltung_id]) {
			this.info = infos[this.lehrveranstaltung_id];
		} else {
			axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Mylv/Info/' + this.studien_semester + '/' + this.lehrveranstaltung_id).then(res => {
				
				this.info = infos[this.lehrveranstaltung_id] = res.data.retval || [];
			}).catch(() => this.info = {});
		}
	},
	template: /*html*/`

			<!-- debugging print
			<p>{{JSON.stringify(info,null,2)}}</p>
			-->
			<h1>{{$p.t('lvinfo/lehrveranstaltungsinformationen')}}</h1>
			<hr>
				<div v-if="!info" class="text-center">
					<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
				</div>
				<table v-else class="table table-hover mb-4">
					<tbody>
						<tr>
							<th>{{$p.t('lehre/lehrveranstaltung')}}</th>
							<td>{{bezeichnung}}</td>
						</tr>
						<tr>
							<th>{{$p.t('lehre/studiengang')}}</th>
							<td>{{studiengang_kuerzel}}</td>
						</tr>
						<tr>
							<th>{{$p.t('lehre/semester')}}</th>
							<td>{{semester}}</td>
						</tr>
						<tr>
							<th>{{$p.t('lehre/studiensemester')}}</th>
							<td>{{studien_semester}}</td>
						</tr>
						<tr>
							<th>{{$p.t('lehre/organisationsform')}}</th>
							<td>{{orgform_kurzbz}}</td>
						</tr>
						<tr>
							<th>{{$p.t('lehre/lehrbeauftragter')}}</th>
							<td>
								<ul v-if="lektorNames.length" class="list-unstyled mb-0">
									<li v-for="name in lektorNames" :key="name">
										<!-- TODO(chris): link? -->
										{{name}}
									</li>
								</ul>
								<template v-else>
									{{$p.t('lehre/keinLektorZugeordnet')}}
								</template>
							</td>
						</tr>
						<tr v-if="lvLeitung">
							<th>{{$p.t('lehre/lvleitung')}}</th>
							<td>
								<ul class="list-unstyled mb-0">
									<li v-for="name in lvLeitung" :key="name">
										<!-- TODO(chris): link? -->
										{{name}}
									</li>
								</ul>
							</td>
						</tr>
						<tr>
							<th>{{$p.t('global/sprache')}}</th>
							<td>{{sprache}}</td>
						</tr>
						<tr>
							<th>{{$p.t('lehre/ects')}}</th>
							<td>{{ects}}</td>
						</tr>
						<tr>
							<th>{{$p.t('lehre/incomingplaetze')}}</th>
							<td>{{incoming}}</td>
						</tr>
						<tr>
							<th>{{$p.t('lehre/organisationseinheit')}}</th>
							<td>
								{{oe}} <br>
								(
								<i>{{$p.t('global/leitung')}}: </i>{{oeLeitung.join(', ')}}
								<template v-if="koordinator">
									<i>{{$p.t('global/koordination')}}: </i>{{koordinator.join(', ')}}
								</template>
								)
							</td>
						</tr>
					</tbody>
				</table>
	
			
				<div v-if="info && info.lvinfo">
					<div v-if="Object.keys(info.lvinfo).length > 1" class="text-end">
						<div class="btn-group" role="group" :title="$p.t('global/verfuegbareSprachen')" :aria-label="$p.t('global/verfuegbareSprachen')">
							<template v-for="lang in info.sprachen" :key="lang.index">
								<button v-if="info.lvinfo[lang.sprache]" type="button" class="btn btn-outline-primary" :class="lang.sprache == currentLang ? 'active' : ''" @click.prevent="info.lastLang = lang.sprache">{{lang.bezeichnung[lang.index-1]}}</button>
							</template>
						</div>
					</div>
					<template v-for="i in info.lvinfo[currentLang]" :key="info">
						<h4>{{i.header}}</h4>
						<h6 v-if="i.subheader">{{i.subheader}}</h6>
						<ul v-if="Array.isArray(i.body)">
							<li v-for="e in i.body" :key="e">{{e}}</li>
						</ul>
						<p v-else>
							{{i.body}}
						</p>
					</template>
				</div>`
}
