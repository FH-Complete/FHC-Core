import AbstractWidget from './Abstract';
import BaseOffcanvas from '../Base/Offcanvas';

export default {
    name: 'WidgetsAmpel',
    components: { BaseOffcanvas },
    data: () => ({
        filter: '',
        source: '',
        ampeln: []
    }),
    mixins: [
        AbstractWidget
    ],
    computed: {
        widgetAmpeln () {
            return this.ampeln.slice(0, 4);  // show only newest 4 ampeln
        },
        offcanvasAmpeln ()
        {
            switch(this.filter)
            {
                case 'verpflichtend': return this.ampeln.filter(item => item.verpflichtend);
                case 'ueberfaellig': return this.ampeln.filter(item => (new Date() > new Date(item.deadline)) && !item.bestaetigt);
                default: return this.ampeln;
            }
        },
        count () {
            return {
                verpflichtend: this.ampeln.filter(item => item.verpflichtend).length,
                ueberfaellig: this.ampeln.filter(item => (new Date() > new Date(item.deadline)) && !item.bestaetigt).length,
                alle: this.ampeln.length
            }
        }
    },
    methods: {
        closeOffcanvasAmpeln()
        {
            for (let i = 0; i < this.offcanvasAmpeln.length; i++)
            {
                let ampelId = this.offcanvasAmpeln[i].ampel_id;
                this.$refs['ampelCollapse_' + ampelId][0].classList.remove('show');
            }
        },
        openOffcanvasAmpel(ampelId){
            // Close earlier opened Ampeln
            this.closeOffcanvasAmpeln();

            // Show given Ampel
            this.$refs['ampelCollapse_' + ampelId][0].classList.add('show');
        },
        closeOffcanvas(){
            this.filter = '';
        },
        confirm(ampelId){
            let indexToRemove = this.ampeln.findIndex((obj => obj.ampel_id === ampelId));
            this.ampeln.splice(indexToRemove, 1);
        },
        changeDisplay(){
            this.filter = '';
            if (this.source == 'offen')
            {
                this.ampeln = TEST_OFFENE_AMPELN;
            }

            if (this.source == 'alle')
            {
                this.ampeln = TEST_ALLE_AMPELN;
                // axios
                //     .get(this.apiurl + '/dashboard/Api/getAmpeln')
                //     .then(res => { this.ampeln = res.data })
                //     .catch(err => { console.error('ERROR: ', err.response.data) });
            }
        },
        validateBtnTxt(buttontext){
            return buttontext == null ? 'Bestätigen' : buttontext;
        }
    },
    created() {
        this.$emit('setConfig', false);
        this.ampeln = TEST_OFFENE_AMPELN;
    },
    template: `
    <div class="widgets-ampel w-100 h-100">
        <div class="d-flex flex-column justify-content-between">
            <div class="d-flex">
                <header class="me-auto"><b>Neueste Ampeln</b></header>
                <div class="mb-2 text-danger"><a href="#allAmpelOffcanvas" data-bs-toggle="offcanvas">Alle Ampeln</a></div>
            </div>
            <div class="d-flex justify-content-end">
                <a v-if="count.ueberfaellig > 0" href="#allAmpelOffcanvas" data-bs-toggle="offcanvas" @click="filter = 'ueberfaellig'" class="text-decoration-none"><span class="badge bg-danger me-1"><i class="fa fa-solid fa-bolt"></i> Überfällig: <b>{{ count.ueberfaellig }}</b></span></a>
            </div>
            <div v-for="ampel in widgetAmpeln" :key="ampel.ampel_id" class="mt-2">
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
            <div v-if="ampeln.length == 0" class="card card-body mt-4 p-4 text-center">
                <span class="text-success h2"><i class="fa fa-solid fa-circle-check"></i></span>
                <span class="text-success h5">Super!</span><br>
                <span class="small">Keine offenen Ampeln.</span>
            </div>
        </div>
    </div>

    <!-- All Ampeln Offcanvas -->
    <BaseOffcanvas id="allAmpelOffcanvas" :closeFunc="closeOffcanvas">
        <template #title><header><b>Alle meine Ampeln</b></header></template>
        <template #body>
            <div class="d-flex justify-content-evenly">
                <div class="form-check form-check-inline form-control-sm">
                <input class="form-check-input" type="radio" v-model="source" id="offen" value="offen" @change="changeDisplay" checked>
                <label class="form-check-label" for="offen">Offene Ampeln</label>
            </div>
            <div class="form-check form-check-inline form-control-sm">
                <input class="form-check-input" type="radio" v-model="source" id="alle" value="alle" @change="changeDisplay">
                <label class="form-check-label" for="alle">Alle Ampeln</label>
            </div>
            </div>
            <div class="col"><button class="btn btn-light w-100" @click="filter = ''"><small>Alle: <b>{{ count.alle }}</b></small></button></div>
            <div class="row row-cols-2 g-2 mt-1">
                <div class="col"><button class="btn btn-danger w-100"  @click="filter = 'ueberfaellig'"><i class="fa fa-solid fa-bolt me-2"></i><small>Überfällig: <b>{{ count.ueberfaellig }}</b></small></button></div>
                <div class="col"><button class="btn btn-warning w-100" @click="filter = 'verpflichtend'"><i class="fa fa-solid fa-triangle-exclamation me-2"></i><small>Pflicht: <b>{{ count.verpflichtend }}</b></small></button></div>
            </div>
            <div v-for="ampel in offcanvasAmpeln" :key="ampel.ampel_id" class="mt-2">
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
                    {{ ampel.beschreibung[0] }}
                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-sm btn-primary" :class="{disabled: ampel.bestaetigt}" @click="confirm(ampel.ampel_id)">{{ validateBtnTxt(ampel.buttontext[0]) }}</button>
                    </div>
                </div>
                </li>
                </ul>
            </div>
        </template>
    </BaseOffcanvas>`
}

const TEST_ALLE_AMPELN = [
    {
        ampel_id: 0,
        kurzbz: 'Ampeltitel 1',
        deadline: '2022-12-31',
        verfallszeit: 10,
        verpflichtend: false,
        bestaetigt: false,
        buttontext: ['Bestätigen', 'Confirm'],
        insertamum: '2022-09-21 15:25:00',
        beschreibung: [
            '1-Deutscher Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.',
            '1-Englischer Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.'
        ]
    },
    {
        ampel_id: 1,
        kurzbz: 'Ampeltitel 2 kann auch etwas länger sein',
        deadline: '2023-10-03',
        verfallszeit: 20,
        verpflichtend: true,
        bestaetigt: false,
        buttontext: ['Gelesen', 'Read'],
        insertamum: '2022-09-21 15:25:00',
        beschreibung: [
            '2-Deutscher Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.',
            '2-Englischer Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.'
        ]
    },
    {
        ampel_id: 2,
        kurzbz: 'Ampeltitel 3',
        deadline: '2022-10-31',
        verfallszeit: null,		// Dauerampel, Bis zur Bestätigung
        verpflichtend: false,
        bestaetigt: true,
        buttontext: ['Bestätigen', 'Confirm'],
        insertamum: '2022-09-21 15:25:00',
        beschreibung: [
            '3-Deutscher Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.',
            '3-Englischer Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.'
        ]
    },
    {
        ampel_id: 3,
        kurzbz: 'Ampeltitel 4',
        deadline: '2022-10-31',
        verfallszeit: 40,
        verpflichtend: false,
        bestaetigt: true,
        buttontext: ['Bestätigen', 'Confirm'],
        insertamum: '2022-09-21 15:25:00',
        beschreibung: [
            'Deutscher Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.',
            'Englischer Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.'
        ]
    },
    {
        ampel_id: 4,
        kurzbz: 'Ampeltitel 5',
        deadline: '2022-10-31',
        verfallszeit: 10,
        verpflichtend: false,
        bestaetigt: true,
        buttontext: ['Bestätigen', 'Confirm'],
        insertamum: '2022-09-21 15:25:00',
        beschreibung: [
            'Deutscher Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.',
            'Englischer Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.'
        ]
    },
    {
        ampel_id: 5,
        kurzbz: 'Ampeltitel 6',
        deadline: '2022-10-31',
        verfallszeit: 40,
        verpflichtend: false,
        bestaetigt: true,
        buttontext: ['Bestätigen', 'Confirm'],
        insertamum: '2022-09-21 15:25:00',
        beschreibung: [
            'Deutscher Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.',
            'Englischer Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.'
        ]
    },
    {
        ampel_id: 6,
        kurzbz: 'Ampeltitel 7',
        deadline: '2020-12-31',
        verfallszeit: 10,
        verpflichtend: false,
        bestaetigt: true,
        buttontext: ['Bestätigen', 'Confirm'],
        insertamum: '2022-09-21 15:25:00',
        beschreibung: [
            'Deutscher Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.',
            'Englischer Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.'
        ]
    },{
        ampel_id: 7,
        kurzbz: 'Ampeltitel 8',
        deadline: '2022-09-25',
        verfallszeit: 40,
        verpflichtend: false,
        bestaetigt: false,
        buttontext: ['Bestätigen', 'Confirm'],
        insertamum: '2022-09-21 15:25:00',
        beschreibung: [
            'Deutscher Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.',
            'Englischer Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.'
        ]
    },
    {
        ampel_id: 8,
        kurzbz: 'Ampeltitel 9',
        deadline: '2022-10-01',
        verfallszeit: 10,
        verpflichtend: false,
        bestaetigt: false,
        buttontext: ['Bestätigen', 'Confirm'],
        insertamum: '2022-09-21 15:25:00',
        beschreibung: [
            'Deutscher Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.',
            'Englischer Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.'
        ]
    }
]

const TEST_OFFENE_AMPELN = [
    {
        ampel_id: 0,
        kurzbz: 'Ampeltitel 1',
        deadline: '2022-12-31',
        verfallszeit: 10,
        verpflichtend: false,
        bestaetigt: false,
        buttontext: ['Bestätigen', 'Confirm'],
        insertamum: '2022-09-21 15:25:00',
        beschreibung: [
            '1-Deutscher Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.',
            '1-Englischer Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.'
        ]
    },
    {
        ampel_id: 1,
        kurzbz: 'Ampeltitel 2 kann auch etwas länger sein',
        deadline: '2023-10-03',
        verfallszeit: 20,
        verpflichtend: true,
        bestaetigt: false,
        buttontext: ['Gelesen', 'Read'],
        insertamum: '2022-09-21 15:25:00',
        beschreibung: [
            '2-Deutscher Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.',
            '2-Englischer Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.'
        ]
    },
    {
        ampel_id: 7,
        kurzbz: 'Ampeltitel 8',
        deadline: '2022-09-25',
        verfallszeit: 40,
        verpflichtend: false,
        bestaetigt: false,
        buttontext: ['Bestätigen', 'Confirm'],
        insertamum: '2022-09-21 15:25:00',
        beschreibung: [
            'Deutscher Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.',
            'Englischer Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.'
        ]
    },
    {
        ampel_id: 8,
        kurzbz: 'Ampeltitel 9',
        deadline: '2022-10-01',
        verfallszeit: 10,
        verpflichtend: false,
        bestaetigt: false,
        buttontext: ['Bestätigen', 'Confirm'],
        insertamum: '2022-09-21 15:25:00',
        beschreibung: [
            'Deutscher Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.',
            'Englischer Text Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod.'
        ]
    }
];