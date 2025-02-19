import AbstractWidget from './Abstract';
import BaseOffcanvas from '../Base/Offcanvas';

let _idcounter = 0;

export default {
	name: 'WidgetsAmpel',
	components: { BaseOffcanvas },
	data() {
		return {
			WIDGET_AMPEL_MAX: 4,
			filter: '',
			source: 'offen',
			allAmpeln:null,
			activeAmpeln:null,
			idcounter: this.configMode ? 0 : ++_idcounter
		};
	},
	mixins: [
		AbstractWidget
	],
	computed: {
		ampelnComputed() {
			switch(this.source) {
				case 'offen': return this.applyFilter(this.activeAmpeln);
				case 'alle': return this.applyFilter(this.allAmpeln);
				default: return this.applyFilter(this.activeAmpeln); 
			}
		},
		ampelnOverview () {
			return this.activeAmpeln?.slice(0, this.WIDGET_AMPEL_MAX);  // show only newest 4 active ampeln
		},
		count () {
			const now = new Date();
			let datasource = this.activeAmpeln;
			if (this.source == 'offen') datasource = this.activeAmpeln;
			if (this.source == 'alle') datasource = this.allAmpeln;

			return {
				verpflichtend: datasource?.filter(item => item.verpflichtend).length,
				ueberfaellig: datasource?.filter(item => (now > new Date(item.deadline)) && !item.bestaetigt).length,
				alle: datasource?.length
			}
		}
	},
	methods: {
		applyFilter(data) {
			switch(this.filter) {
				case 'verpflichtend': return data?.filter(item => item.verpflichtend);
				case 'ueberfaellig': const now = new Date(); return data?.filter(item => (now > new Date(item.deadline)) && !item.bestaetigt);
				default: return data;
			}
		},
		toggleFilter(value) {
			this.filter === value ? this.filter = '' : this.filter = value;
		},
		closeOffcanvasAmpeln() {
			for (let i = 0; i < this.ampelnComputed.length; i++)
			{
				let ampelId = this.ampelnComputed[i].ampel_id;
				if(this.$refs['ampelCollapse_' + ampelId]){
					this.$refs['ampelCollapse_' + ampelId][0].classList.remove('show');
				} 
			}
		},
		openOffcanvasAmpel(ampelId) {
			// Close earlier opened Ampeln
			this.closeOffcanvasAmpeln();

			// Show given Ampel
			this.$refs['ampelCollapse_' + ampelId][0].classList.add('show');
		},
		closeOffcanvas() {
			this.closeOffcanvasAmpeln();
			this.filter = '';
			// maybe we also want to reset the source (open/alle) of the displayed ampeln
		},
		fetchNonConfirmedActiveAmpeln() {
			this.$fhcApi.factory
				.ampeln.open()
				.then(res => {
					this.activeAmpeln = res.data;
				})
				.catch(error => this.$fhcAlert.handleSystemError);
		},
		fetchAllActiveAmpeln() {
			this.$fhcApi.factory
				.ampeln.all()
				.then(res => {
					this.allAmpeln = res.data;
				})
				.catch(error => this.$fhcAlert.handleSystemError);

		},
		async confirm(ampelId) {
			this.$fhcApi.factory
				.ampeln.confirm(ampelId)
				.then(() => {
					this.$fhcAlert.alertSuccess(this.$p.t('ampeln', 'ampelBestaetigt'));
					// update the ampeln by refetching them
					this.fetchNonConfirmedActiveAmpeln();
					this.fetchAllActiveAmpeln();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		validateBtnTxt(buttontext) {
			if (buttontext instanceof Array && !buttontext.length) return this.$p.t('ui', 'bestaetigen');

			if (!buttontext) return this.$p.t('ui', 'bestaetigen');

			return buttontext;
		}
	},
	created() {
		this.$emit('setConfig', false);
	},
	async mounted() {
		if (!this.configMode) {
			this.fetchNonConfirmedActiveAmpeln();
			this.fetchAllActiveAmpeln();
		}
	},
	template: /*html*/`
	<div class="widgets-ampel w-100 h-100" style="padding: 1rem 1rem;">
		<div  v-if="!configMode">
			<div v-if="activeAmpeln" class="d-flex flex-column justify-content-between">
				<div class="d-flex">
					<header class="me-auto"><b>{{$p.t('ampeln', 'newestAmpeln')}}</b></header>
					<div class="mb-2 text-danger">
						<a :href="'#allAmpelOffcanvas' + idcounter" data-bs-toggle="offcanvas">
							{{$p.t('ampeln', 'allAmpeln')}}
						</a>
					</div>
				</div>
				<div class="d-flex justify-content-end">
					<a v-if="count.ueberfaellig > 0" :href="'#allAmpelOffcanvas' + idcounter" data-bs-toggle="offcanvas" @click="filter = 'ueberfaellig'" class="text-decoration-none">
						<span class="badge bg-danger me-1">
							<i class="fa fa-solid fa-bolt"></i> {{$p.t('ampeln','overdue',{count:count.ueberfaellig})}}
						</span>
					</a>
				</div>
				<div v-for="ampel in ampelnOverview" :key="ampel.ampel_id" class="mt-2">
					<div class="card">
						<div class="card-body">
							<div class="position-relative">
								<div class="d-flex">
									<div class="text-muted small me-auto"><small>{{$p.t('ampeln','ampelnDeadline',{value:getDate(ampel.deadline)})}}</small></div>
									<div v-if="(new Date() > new Date(ampel.deadline)) && !ampel.bestaetigt "><span class="badge bg-danger"><i class="fa fa-solid fa-bolt"></i></span></div>
									<div v-if="ampel.verpflichtend"><span class="badge bg-warning ms-1"><i class="fa fa-solid fa-triangle-exclamation"></i></span></div>
									<div v-if="ampel.bestaetigt"><span class="badge bg-success ms-1"><i class="fa fa-solid fa-circle-check"></i></span></div>
								</div>
							</div>
							<a :href="'#allAmpelOffcanvas' + idcounter" data-bs-toggle="offcanvas" class="stretched-link" @click="openOffcanvasAmpel(ampel.ampel_id)">{{ ampel.kurzbz }}</a><br>
						</div>
					</div>
				</div>

				<div v-if="activeAmpeln.length == 0" class="card card-body mt-4 p-4 text-center">
					<span class="text-success h2"><i class="fa fa-solid fa-circle-check"></i></span>
					<span class="text-success h5">{{$p.t('ampeln','super')}}</span><br>
					<span class="small">{{$p.t('ampeln','noOpenAmpeln')}}</span>
				</div>
			</div>
			<div v-else>
				<header class="me-auto"><b>{{$p.t('ampeln', 'newestAmpeln')}} </b></header>
				<template v-for="n in WIDGET_AMPEL_MAX">
					<div class="mt-2 card" aria-hidden="true">
						<div class="card-body">
							<p class="card-text placeholder-glow">
								<span class="placeholder col-7"></span>
								<span class="placeholder col-12"></span>
							</p>
						</div>
					</div>
				</template>
			</div>
		</div>

		<!-- All Ampeln Offcanvas -->
		<BaseOffcanvas v-if="!configMode" :id="'allAmpelOffcanvas' + idcounter" :closeFunc="closeOffcanvas">
			<template #title><header><b>{{$p.t('ampeln','allMyAmpeln')}}</b></header></template>
			<template #body>
				<div class="d-flex justify-content-evenly">
					<div class="form-check form-check-inline form-control-sm">
						<input class="form-check-input" type="radio" v-model="source" :id="'offen' + idcounter" value="offen">
						<label class="form-check-label" :for="'offen' + idcounter">{{$p.t('ampeln','openAmpeln')}}</label>
					</div>
					<div class="form-check form-check-inline form-control-sm">
						<input class="form-check-input" type="radio" v-model="source" :id="'alle' + idcounter" value="alle" >
						<label class="form-check-label" :for="'alle' + idcounter">{{$p.t('ampeln','allAmpeln')}}</label>
					</div>
				</div>
				<div class="col">
					<button class="btn btn-light w-100" @click="filter = ''">
						<small :class="{'fw-bold':filter===''}">{{$p.t('ui','alle')}}: <b>{{ count.alle }}</b></small>
					</button>
				</div>
				<div class="row row-cols-2 g-2 mt-1">
					<div class="col">
						<button class="btn btn-danger w-100"  @click="toggleFilter('ueberfaellig')">
							<i class="fa fa-solid fa-bolt me-2"></i>
							<small :class="{'fw-bold':filter==='ueberfaellig'}">
								{{$p.t('ampeln','overdue',{count:count.ueberfaellig})}}
							</small>
						</button>
					</div>
					<div class="col">
						<button class="btn btn-warning w-100" @click="toggleFilter('verpflichtend')">
							<i class="fa fa-solid fa-triangle-exclamation me-2"></i>
							<small :class="{'fw-bold':filter==='verpflichtend'}" >
								{{$p.t('ampeln','mandatory')}}: <b>{{ count.verpflichtend }}</b>
							</small>
						</button>
					</div>
				</div>
				<div v-for="ampel in ampelnComputed" :key="ampel.ampel_id" class="mt-2">
					<ul class="list-group">
						<li class="list-group-item small">
							<div class="position-relative">
								<!-- prevents streched-link from stretching outside this parent element -->
								<div class="d-flex">
									<span class="small text-muted me-auto">
										<small>
											{{$p.t('ampeln','ampelnDeadline',{value:getDate(ampel.deadline)})}}
										</small>
									</span>
									<div v-if="(new Date() > new Date(ampel.deadline)) && !ampel.bestaetigt">
										<span class="badge bg-danger">
											<i class="fa fa-solid fa-bolt"></i>
										</span>
									</div>
									<div v-if="ampel.verpflichtend">
										<span class="badge bg-warning ms-1">
											<i class="fa fa-solid fa-triangle-exclamation"></i>
										</span>
									</div>
									<div v-if="ampel.bestaetigt">
										<span class="badge bg-success ms-1">
											<i class="fa fa-solid fa-circle-check"></i>
										</span>
									</div>
								</div>
								<a :href="'#ampelCollapse' + idcounter + '_' + ampel.ampel_id" data-bs-toggle="collapse" class="stretched-link">
									{{ ampel.kurzbz }}
								</a>
								<br>
							</div>
							<div class="collapse my-3" :id="'ampelCollapse' + idcounter + '_' + ampel.ampel_id" :ref="'ampelCollapse_' + ampel.ampel_id">
								<div v-html="ampel.beschreibung_trans"></div>
								<div v-if="!ampel.bestaetigt " class="d-flex justify-content-end mt-3">
									<button class="btn btn-sm btn-primary" :class="{disabled: ampel.bestaetigt}" @click="confirm(ampel.ampel_id)">
										{{ validateBtnTxt(ampel.buttontext_trans) }}
									</button>
								</div>
							</div>
						</li>
					</ul>
				</div>
			</template>
		</BaseOffcanvas>
	</div>`
}

