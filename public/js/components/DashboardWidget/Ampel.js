import AbstractWidget from './Abstract';
import BaseOffcanvas from '../Base/Offcanvas';

const widgetAmpelMAX = 4;

export default {
    name: 'WidgetsAmpel',
    components: { BaseOffcanvas },
    data: () => ({
        filter: '',
        source: '',
        ampeln: null,
        allAmpeln:null,
        activeAmpeln:null,
    }),
    mixins: [
        AbstractWidget
    ],
    computed: {
        widgetAmpelMAX(){
            return widgetAmpelMAX;
        },
        ampelnComputed(){
            
            switch(this.source)
            {
                case 'offen': return this.applyFilter(this.activeAmpeln);
                case 'alle': return this.applyFilter(this.allAmpeln);
                default: return this.applyFilter(this.activeAmpeln); 
            }
            
        },
        ampelnOverview () {
            return this.activeAmpeln?.slice(0, widgetAmpelMAX);  // show only newest 4 active ampeln
        },
        count () {
            let datasource = this.activeAmpeln;
            if (this.source == 'offen') datasource = this.activeAmpeln;
            if (this.source == 'alle') datasource = this.allAmpeln;

            return {
                verpflichtend: datasource?.filter(item => item.verpflichtend).length,
                ueberfaellig: datasource?.filter(item => (new Date() > new Date(item.deadline)) && !item.bestaetigt).length,
                alle: datasource?.length
            }
            
        }
    },
    methods: {
        applyFilter(data){
            switch(this.filter)
                {
                    case 'verpflichtend': return data?.filter(item => item.verpflichtend);
                    case 'ueberfaellig': return data?.filter(item => (new Date() > new Date(item.deadline)) && !item.bestaetigt);
                    default: return data;
                }
        },
        

        toggleFilter(value){
            this.filter === value ? this.filter = '' : this.filter = value;
        },
        
        closeOffcanvasAmpeln()
        {
            for (let i = 0; i < this.ampelnComputed.length; i++)
            {
                let ampelId = this.ampelnComputed[i].ampel_id;
                if(this.$refs['ampelCollapse_' + ampelId]){
                    this.$refs['ampelCollapse_' + ampelId][0].classList.remove('show');
                } 
            }
        },
        openOffcanvasAmpel(ampelId){
            // Close earlier opened Ampeln
            this.closeOffcanvasAmpeln();

            // Show given Ampel
            this.$refs['ampelCollapse_' + ampelId][0].classList.add('show');
        },
        closeOffcanvas(){
            this.closeOffcanvasAmpeln();
            this.filter = '';
            // maybe we also want to reset the source (open/alle) of the displayed ampeln
        },
        async fetchNonConfirmedActiveAmpeln(){

            await this.$fhcApi.factory.ampeln.getNonConfirmedActiveAmpeln().then(res=>{
                this.activeAmpeln = res.data.sort((a,b) => new Date(b.deadline) - new Date(a.deadline));
            }); 
        },
        async fetchAllActiveAmpeln(){

            await this.$fhcApi.factory.ampeln.getAllActiveAmpeln().then(res=>{
                this.allAmpeln = res.data.sort((a,b) => new Date(b.deadline) - new Date(a.deadline));
            }); 
        },
        async confirm(ampelId){
            this.$fhcApi.factory.ampeln.confirmAmpel(ampelId).then(res =>{
                // response of the enpoint when confirming an ampel (true if confirmed and false if not confirmed)
                if(res.data){
                    console.log("ampel was successfully confirmed");
                }else{
                    console.error("ampel was not successfully confirmed");
                } 
            });

            // update the ampeln by refetching them
            this.fetchNonConfirmedActiveAmpeln();
            this.fetchAllActiveAmpeln();
        },
        validateBtnTxt(buttontext){

            if(buttontext instanceof Array && !buttontext.length) return 'Bestätigen';

            if(!buttontext) return 'Bestätigen';

            return buttontext;
        }
    },
    created() {

        this.$emit('setConfig', false);    
    },
    async mounted() {
        
        await this.fetchNonConfirmedActiveAmpeln();
        await this.fetchAllActiveAmpeln();
    },
    template: /*html*/`
    <div class="widgets-ampel w-100 h-100">
        <div v-if="activeAmpeln" class="d-flex flex-column justify-content-between">
            <div class="d-flex">
                <header class="me-auto"><b>Neueste Ampeln</b></header>
                <div class="mb-2 text-danger"><a href="#allAmpelOffcanvas" data-bs-toggle="offcanvas">Alle Ampeln</a></div>
            </div>
            <div class="d-flex justify-content-end">
                <a v-if="count.ueberfaellig > 0" href="#allAmpelOffcanvas" data-bs-toggle="offcanvas" @click="filter = 'ueberfaellig'" class="text-decoration-none"><span class="badge bg-danger me-1"><i class="fa fa-solid fa-bolt"></i> Überfällig: <b>{{ count.ueberfaellig }}</b></span></a>
            </div>
            <div v-for="ampel in ampelnOverview" :key="ampel.ampel_id" class="mt-2">
                <div class="card">
                    <div class="card-body">
                        <div class="position-relative">
                            <div class="d-flex">
                                <div class="text-muted small me-auto"><small>Deadline: {{ getDate(ampel.deadline) }}</small></div>
                                <div v-if="(new Date() > new Date(ampel.deadline)) && !ampel.bestaetigt "><span class="badge bg-danger"><i class="fa fa-solid fa-bolt"></i></span></div>
                                <div v-if="ampel.verpflichtend"><span class="badge bg-warning ms-1"><i class="fa fa-solid fa-triangle-exclamation"></i></span></div>
                                <div v-if="ampel.bestaetigt"><span class="badge bg-success ms-1"><i class="fa fa-solid fa-circle-check"></i></span></div>
                            </div>
                        </div>
                        <a href="#allAmpelOffcanvas" data-bs-toggle="offcanvas" class="stretched-link" @click="openOffcanvasAmpel(ampel.ampel_id)">{{ ampel.kurzbz }}</a><br>
                    </div>
                </div>
            </div>
            
            <div v-if="activeAmpeln && activeAmpeln.length == 0" class="card card-body mt-4 p-4 text-center">
                <span class="text-success h2"><i class="fa fa-solid fa-circle-check"></i></span>
                <span class="text-success h5">Super!</span><br>
                <span class="small">Keine offenen Ampeln.</span>
            </div>
        </div>
        <div v-else>
        <header class="me-auto"><b>Neueste Ampeln </b></header>
            <template v-for="n in widgetAmpelMAX">
            
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
    <BaseOffcanvas id="allAmpelOffcanvas" :closeFunc="closeOffcanvas">
        <template #title><header><b>Alle meine Ampeln</b></header></template>
        <template #body>
            <div class="d-flex justify-content-evenly">
                <div class="form-check form-check-inline form-control-sm">
                <input class="form-check-input" type="radio" v-model="source" id="offen" value="offen"  checked>
                <label class="form-check-label" for="offen">Offene Ampeln</label>
            </div>
            <div class="form-check form-check-inline form-control-sm">
                <input class="form-check-input" type="radio" v-model="source" id="alle" value="alle" >
                <label class="form-check-label" for="alle">Alle Ampeln</label>
            </div>
            </div>
            <div class="col"><button class="btn btn-light w-100" @click="filter = ''"><small>Alle: <b>{{ count.alle }}</b></small></button></div>
            <div class="row row-cols-2 g-2 mt-1">
                <div class="col"><button class="btn btn-danger w-100"  @click="toggleFilter('ueberfaellig')"><i class="fa fa-solid fa-bolt me-2"></i><small :style="{...(filter==='ueberfaellig'?{'text-decoration':'underline','font-weight':'bold'}:{})}">Überfällig: <b>{{ count.ueberfaellig }}</b></small></button></div>
                <div class="col"><button class="btn btn-warning w-100" @click="toggleFilter('verpflichtend')"><i class="fa fa-solid fa-triangle-exclamation me-2"></i><small :style="{...(filter==='verpflichtend'?{'text-decoration':'underline','font-weight':'bold'}:{})}">Pflicht: <b>{{ count.verpflichtend }}</b></small></button></div>
            </div>
            <div v-for="ampel in ampelnComputed" :key="ampel.ampel_id" class="mt-2">
                <ul class="list-group">
                <li class="list-group-item small">
                <div class="position-relative"><!-- prevents streched-link from stretching outside this parent element -->
                    <div class="d-flex">
                        <span class="small text-muted me-auto"><small>Deadline: {{ getDate(ampel.deadline) }}</small></span>
                        <div v-if="(new Date() > new Date(ampel.deadline)) && !ampel.bestaetigt"><span class="badge bg-danger"><i class="fa fa-solid fa-bolt"></span></div>
                        <div v-if="ampel.verpflichtend"><span class="badge bg-warning ms-1"><i class="fa fa-solid fa-triangle-exclamation"></span></div>
                        <div v-if="ampel.bestaetigt"><span class="badge bg-success ms-1"><i class="fa fa-solid fa-circle-check"></i></span></div>
                    </div>
                    <a :href="'#ampelCollapse_' + ampel.ampel_id" data-bs-toggle="collapse" class="stretched-link">{{ ampel.kurzbz }}</a><br>
                </div>
                <div class="collapse my-3" :id="'ampelCollapse_' + ampel.ampel_id" :ref="'ampelCollapse_' + ampel.ampel_id">
                    <div v-html="ampel.beschreibung"></div>
                    <div v-if="!ampel.bestaetigt " class="d-flex justify-content-end mt-3">
                        <button  class="btn btn-sm btn-primary" :class="{disabled: ampel.bestaetigt}" @click="confirm(ampel.ampel_id)">{{ validateBtnTxt(ampel.buttontext) }}</button>
                    </div>
                </div>
                </li>
                </ul>
            </div>
        </template>
    </BaseOffcanvas>`
}

