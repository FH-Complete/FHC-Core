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
	<template v-if="Array.isArray(data) && data.length >0">
    <div class="card">
        <div class="card-header">
            {{title}}
        </div>
        <div class="card-body">
            <h4 class="card-title">{{$p.t('profil','mailverteilerMitglied')}}</h4>
            <div class="card-text row text-break mb-2" v-for="verteiler in data">
                <div class="col-12 ">
                    <div class="row">
                        <div class="col-1 ">
                            <i class="fa-solid fa-envelope fhc-link-color" ></i>
                        </div>
                        <div class="col">
                            <a class="fhc-link-color" :href="verteiler.mailto"><b>{{verteiler.gruppe_kurzbz}}</b></a>
                        </div>
                    </div>
                </div>
                <div class="col-11 offset-1 ">{{verteiler.beschreibung}}</div>
            </div>
        </div>
    </div>
	</template>`,
};
