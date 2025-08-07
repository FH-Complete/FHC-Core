export default{
    props:{
        view:String,
        data:Object,
    },
    data(){
        return {

        }
    },
    created(){

    }, 
    template:/*html*/`
	<template v-if="data.kontakt">
    <div class="gy-2 row align-items-center justify-content-center">
        <div class="col-1 text-center" >
            <i class="fa-solid fhc-link-color" :class="{...(data.kontakt.includes('@')?{'fa-envelope':true}:{'fa-phone':true})}" ></i>
        </div>
        <div :class="{...(data.anmerkung? {'col-11':true, 'col-md-6':true, 'col-xl-11':true, 'col-xxl-6':true} : {'col-10':true, 'col-xl-9':true, 'col-xxl-10':true})}">
            <!-- rendering KONTAKT emails -->
            <div  class="form-underline ">
                <div class="form-underline-titel">{{$p.t('profil',data.kontakttyp.toUpperCase())}}</div>
                <a v-if="data.kontakt.includes('@')" role="link" :aria-disabled="view?true:false" :href="!view?('mailto:'+data.kontakt):null" class="form-underline-content">{{data.kontakt}} </a>
                <a v-else role="link" :aria-disabled="view?true:false" :href="!view?('tel:'+data.kontakt):null" class="form-underline-content">{{data.kontakt}} </a>
            </div>
        </div>
        <div v-if="data?.anmerkung" class="offset-1 offset-md-0 offset-xl-1 offset-xxl-0 order-2 order-sm-1 col-10  col-md-4   col-xl-9 col-xxl-4   ">
            <div  class="form-underline ">
                <div class="form-underline-titel">{{$p.t('global','anmerkung')}}</div>
                <span  class="form-underline-content">{{data.anmerkung}} </span>
            </div>
        </div>
        <div class="text-center col-1 col-sm-1 order-2  order-lg-1 col-xl-2 col-xxl-1 allign-middle">
            <i v-if="data.zustellung" class="fa-solid fa-check"></i>
            <i v-else="data.zustellung" class="fa-solid fa-xmark"></i>
        </div>
    </div>
	</template>
`,
};