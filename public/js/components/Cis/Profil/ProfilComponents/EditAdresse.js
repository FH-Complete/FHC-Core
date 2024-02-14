export default {
    props:{
        data:Object,
        isMitarbeiter:{
            type: Boolean,
            default: false,
        },
    },
    data(){
        return{
            originalValue:null,
            zustellAdressenCount:null,
        }
    },
    methods:{
        updateValue: function(event,bind){
            //? sets the value of a property to null when an empty string is entered to keep the isChanged function valid 
            if(bind ==="zustelladresse" ){
                this.data[bind] = event.target.checked;
            }else{
                this.data[bind] = event.target.value === "" ? null : event.target.value;
            }
            
            this.$emit('profilUpdate',this.isChanged?this.data:null);
        },
    },
    computed:{
        showZustellAdressenWarning: function(){
            if(this.zustellAdressenCount){
                
                if(this.zustellAdressenCount.includes(this.data.adresse_id)){
                    //? if the adresse was already saved
                    return false;
                }
                return this.zustellAdressenCount>0 && this.data.zustelladresse;
            }
            //? if this.zustellAdressenCount is still not set by the api call and is still null
            return false;
            

        },
        ortLayoutClasses: function(){
            return this.showKontaktTyp?[
                'col-12', 
                'col-sm-7',
                'col-xl-12',
                'col-xxl-7',
            ]:['col-12'];
        },
        showKontaktTyp: function(){
            let kontaktTypen = ["Nebenwohnsitz","Hauptwohnsitz"];
            return kontaktTypen.includes(this.data.typ) || !this.data.typ
        },
        isChanged: function(){
            if(!this.data.strasse || !this.data.plz || !this.data.ort || !this.data.typ){
               
                return false;
            }
            return this.originalValue !== JSON.stringify(this.data);
        },
    },
    created(){
        Vue.$fhcapi.UserData.getZustellAdresse().then(res => {
            
            this.zustellAdressenCount = res.data?.length;
        })
        this.originalValue = JSON.stringify(this.data);
        
    },
    template:`
   
     <div class="gy-3 row justify-content-center align-items-center">
     
     
     <!-- warning message for too many zustellungs Adressen -->
     <div v-if="showZustellAdressenWarning" class="col-12 ">
     <div class="card bg-danger mx-2">
     <div class="card-body text-white ">
     <span>!Achtung: Eine deiner Adressen ist bereits als Zustelladresse gespeichert, sind sie sicher, dass sie die aktuelle Adresse stattdessen als Zustelladresse speichern wollen?</span>
     </div>
     </div>
     </div>
     <!-- End of warning -->


     <div class="col-12 ">
        
       
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" @change="updateValue($event,'zustelladresse')" :checked="data.zustelladresse" id="flexCheckDefault">
                <label class="form-check-label" for="flexCheckDefault">
                    Zustelladresse
                </label>
            </div>
        
       
           
            
            
        </div>
      
        <div  class="col-12 col-sm-9 col-xl-12 col-xxl-9 order-1">

        <div class="form-underline ">
        <div class="form-underline-titel">Strasse</div>
        <input  class="form-control" :value="data.strasse" @input="updateValue($event,'strasse')" :placeholder="data.strasse">
        
        </div>


        </div>
        
        <div class=" order-2 order-sm-4 order-xl-3 order-xxl-4 col-12 col-sm-5  col-xl-8 col-xxl-5  ">
            
            <div  class="form-underline">
                <div class="form-underline-titel">Kontakttyp</div>
        
                <select  :value="data.typ" @change="updateValue($event,'typ')" class="form-select" aria-label="Select Kontakttyp">
                    <option selected></option>
                    <option value="Nebenwohnsitz">Nebenwohnsitz</option>
                    <option value="Hauptwohnsitz">Hauptwohnsitz</option>
                    <option v-if="isMitarbeiter" value="Homeoffice">Homeoffice</option>
                    <option v-if="isMitarbeiter" value="Rechnungsadresse">Rechnungsadresse</option>
                  
                </select>    
            </div>
        

            
            </template>

        </div>

        <div  class="order-3 order-sm-3 order-xl-2 order-xxl-3 col-12 col-sm-7 col-xl-12 col-xxl-7 " >
            
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