


const Adresse = {
    props:{data:Object, view:String},
    data(){
        return{}
    },
    created(){
        
    },
    template:`
    <div class="my-2 row justify-content-center align-items-center">
    
    <!-- column 1 in the address row -->
    
        <div class="col-1 text-center">
         
          <i class="fa fa-location-dot fa-lg" style="color:#00649C "></i>
        
        </div>
        <div  class="col-11 col-sm-8 col-xl-11 col-xxl-8 order-1">

        <div class="form-underline ">
        <div class="form-underline-titel">Strasse</div>
        <span class="form-underline-content">{{data.strasse}} </span>
        </div>


        </div>
        
    <!-- column 2 in the address row -->
        <div  class="offset-1 offset-sm-0 offset-xl-1 offset-xxl-0 order-2 order-sm-4 order-xl-2 order-xxl-4 col-11 col-sm-5  col-xl-11 col-xxl-5  ">
            

            <div class="form-underline ">
            <div class="form-underline-titel">Typ</div>
            <span class="form-underline-content">{{data.adr_typ}} </span>
            </div>

        </div>
        <div  class="offset-1 order-3 order-sm-3 col-11 col-sm-6  col-xl-7 col-xxl-6 ">
            
            <div class="form-underline ">
            <div class="form-underline-titel">Ort</div>
            <span class="form-underline-content">{{data.ort}} </span>
            </div>
        </div>
        <div  class="offset-1 offset-sm-0 order-4 order-sm-2 order-xl-4 order-xxl-2 col-11 col-sm-3 col-xl-4 col-xxl-3 ">
            <div class="form-underline ">
            <div class="form-underline-titel">PLZ</div>
            <span class="form-underline-content">{{data.plz}} </span>
            </div>
        </div>
    </div>
    `

};

const EditAdresse = {
    props:{data:Object},
    data(){
        return{}
    },
    methods:{
        updateValue: function(event,bind){
            this.data[bind] = event.target.value;
            this.$emit('profilUpdate',this.data);
        },
    },
    created(){
        
    },
    template:`
    <div class="gy-3 row justify-content-center align-items-center">
    
    <!-- column 1 in the address row -->
    
      
        <div  class="col-12 col-sm-9 col-xl-12 col-xxl-9 order-1">

        <div class="form-underline ">
        <div class="form-underline-titel">Strasse</div>
        <input  class="form-control" :value="data.strasse" @input="updateValue($event,'strasse')" :placeholder="data.strasse">
        
        </div>


        </div>
        
    <!-- column 2 in the address row -->
        <div  class=" order-2 order-sm-4 order-xl-3 order-xxl-4 col-12 col-sm-5  col-xl-8 col-xxl-5  ">
            

            <div class="form-underline ">
            <div class="form-underline-titel">Typ</div>
            <input  class="form-control" :value="data.adr_typ" @input="updateValue($event,'adr_typ')" :placeholder="data.adr_typ">
        
            </div>

        </div>
        <div  class="order-3 order-sm-3 order-xl-2 order-xxl-3 col-12 col-sm-7  col-xl-12 col-xxl-7 ">
            
            <div class="form-underline ">
            <div class="form-underline-titel">Ort</div>
            <input  class="form-control" :value="data.ort" @input="updateValue($event,'ort')" :placeholder="data.ort">
        
            </div>
        </div>
        <div  class="order-4 order-sm-2 order-xl-4 order-xxl-2 col-12 col-sm-3 col-xl-4 col-xxl-3 ">
            <div class="form-underline ">
            <div class="form-underline-titel">PLZ</div>
    
            <input  class="form-control" :value="data.plz" @input="updateValue($event,'plz')" :placeholder="data.plz">
        
            </div>
        </div>
    </div>
    `

};

const Kontakt = {
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
    template:`
    <div class=" row align-items-center justify-content-center">
    <div class="col-1 text-center" >
    
    <i class="fa-solid " :class="{...(data.kontakt.includes('@')?{'fa-envelope':true}:{'fa-phone':true})}" style="color:rgb(0, 100, 156)"></i>
    </div>
    <div  :class="{...(data.anmerkung? {'col-11':true, 'col-md-6':true, 'col-xl-11':true, 'col-xxl-6':true} : {'col-10':true, 'col-xl-9':true, 'col-xxl-10':true})}">
        
        <!-- rendering KONTAKT emails -->
   
        
        <div  class="form-underline ">
        <div class="form-underline-titel">{{data.kontakttyp}}</div>
        <a v-if="data.kontakt.includes('@')" role="link" :aria-disabled="view?true:false" :href="!view?('mailto:'+data.kontakt):null" class="form-underline-content">{{data.kontakt}} </a>
        <a v-else role="link" :aria-disabled="view?true:false" :href="!view?('tel:'+data.kontakt):null" class="form-underline-content">{{data.kontakt}} </a>
        </div>
          
       

    </div>
    <div v-if="data?.anmerkung" class="offset-1 offset-md-0 offset-xl-1 offset-xxl-0 order-2 order-sm-1 col-10  col-md-4   col-xl-9 col-xxl-4   ">
        
    <div  class="form-underline ">
    <div class="form-underline-titel">Anmerkung</div>
    <span  class="form-underline-content">{{data.anmerkung}} </span>
    </div>

  
    </div>
    <div class="text-center col-1 col-sm-1 order-2  order-lg-1 col-xl-2 col-xxl-1 allign-middle">
        <i v-if="data.zustellung" class="fa-solid fa-check"></i>
        <i v-else="data.zustellung" class="fa-solid fa-xmark"></i>
    </div>
  </div>
    
    
    
    
    `,
};

const EditKontakt =  {
    props:{
        data:Object,
    },
    data(){
        return{

        }
    },
    methods:{
        updateValue: function(event,bind){
            if(bind === 'zustellung'){
                this.data[bind] = event.target.checked;    
            }else{
                this.data[bind] = event.target.value;
            }
            
            this.$emit('profilUpdate',this.data);
        },
    },
    created(){
        
    },
    template:
    `
   
    <div class="gy-3 row align-items-center justify-content-center">
    
   
    <div class="col-12">
        
        <!-- rendering KONTAKT emails -->
   

        <div  class="form-underline">
        <div class="form-underline-titel">{{data.kontakttyp}}</div>
    
        <input  class="form-control"   :value="data.kontakt" @input="updateValue($event,'kontakt')" :placeholder="data.kontakt">
        </div>

    
          
       

    </div>
    <div v-if="data?.anmerkung" class="col-12">
        

    <div  class="form-underline">
    <div class="form-underline-titel">Anmerkung</div>

    <input  class="form-control" :value="data.anmerkung" @input="updateValue($event,'anmerkung')" :placeholder="data.anmerkung">
    </div>

  

  
    </div>
    
  
  
  
    <div  class="d-flex flex-row justify-content-start col-12  allign-middle">
        
   
    
        <span style="opacity: 0.65; font-size: .85rem; " class="px-2">Zustellungs Kontakt</span>

        <input class="form-check-input " type="checkbox" :checked="data.zustellung" @change="updateValue($event,'zustellung')" id="flexCheckDefault">
    
    
 

   
    </div>
  </div>
    `
};


//? used to edit already requested profil changes
import EditProfil from "./EditProfil.js";
const FetchProfilUpdates = {
    props:{
        data:{
            type:Object,
        },
    },

    emits:["fetchUpdates"],
    
    data(){
        return {
            
        }
    },
    methods:{
        deleteRequest: function(item){
            
            Vue.$fhcapi.UserData.deleteProfilRequest(item.profil_update_id).then((res)=>{
                if(res.data.error){
                    //? open alert
                    console.log(res.data);                    
                }else{
                    this.$emit('fetchUpdates');
                }
            });
        },
        getView: function(topic){
            switch(topic){
                case "Private Kontakte" : return "EditKontakt"; break;
                case "Private Adressen" : return "EditAdresse"; break;
                default: return "text_input"; break;
            }
        },
        openModal(updateRequest) {

            let view = this.getView(updateRequest.topic);
            let content =null;
            if(view === 'text_input'){
                content={
                    view:view,
                    data:{
                        titel:updateRequest.topic,
                        value:updateRequest.requested_change,
                    },
                    update:true,
                    topic:updateRequest.topic,
                    
                }
            }else{
                content = {
                    view: view,
                    data: updateRequest.requested_change,
                    update:true,
                    topic:updateRequest.topic,
                    
                    
                }
            }


            //? only show the popup if also the right content is available
            if(content){
            EditProfil.popup({ 
                
                value:content,
                timestamp:null,
              }).then((res) => {
                if(res === true){
                    this.$emit('fetchUpdates');
                }
                
              }).catch(e => {
                console.log(e);
               
              });
            
            }
           
          
          }, 
    },
    created(){
        
    },
    computed:{
        
    },
    template:`
    <div class="table-responsive">
        <table class="m-0  table  table-hover">
            <thead>
                <tr >
                <th scope="col">Topic</th>
                <th scope="col">Date of Request</th>
                <th scope="col">Bearbeiten</th>
                <th style="white-space:normal" scope="col">Löschen</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in data">
                <td class="align-middle">{{item.topic}}</td>
                <td class="align-middle">{{item.change_timestamp}}</td>
                <td class="align-middle text-center" ><i style="color:#00639c" @click="openModal(item)" role="button" class="fa fa-edit"></i></td>
                <td class="align-middle text-center"><i style="color:red" role="button" @click="deleteRequest(item)" class="fa fa-trash"></i></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    `
};

export {Adresse, EditAdresse, Kontakt, EditKontakt, FetchProfilUpdates};