export default {
    props: {
        data: Object,
        title: { type: String },
    },
    data() {
        return {};
    },
    created(){

    },
    template: /*html*/`
    <div class="card">
        <div class="card-header">
            {{title}}
        </div>
        <div class="card-body">
            <h6 class="card-title">{{$p.t('profil','mailverteilerMitglied')}}</h6>
            <div class="card-text row text-break mb-2" v-for="verteiler in data">
                <div class="col-12 ">
                    <div class="row">  
                        <div class="col-1 ">
                            <i class="fa-solid fa-envelope" style="color: #00649C;"></i>
                        </div>
                        <div class="col">
                            <a :href="verteiler.mailto"><b>{{verteiler.gruppe_kurzbz}}</b></a>
                        </div>
                    </div>
                </div> 
                <div class="col-11 offset-1 ">{{verteiler.beschreibung}}</div>
            </div>
        </div>
    </div>`,
};
