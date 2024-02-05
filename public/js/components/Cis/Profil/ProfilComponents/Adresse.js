export default {
    props:{
        data:Object,
        view:String,
        withZustelladresse:{
            type:Boolean,
            default:false,
        },
    },
    data(){
        return{}
    },
    created(){
        
    },
    template:`
    
    <div class="gy-2 row justify-content-center align-items-center">
    
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
            <span class="form-underline-content">{{data.typ}} </span>
            </div>

        </div>
        <div  class="offset-1 order-3 order-sm-3 col-11 col-sm-6  col-xl-7 col-xxl-6 ">
            
            <div class="form-underline ">
            <div class="form-underline-titel">Ort</div>
            <span class="form-underline-content">{{data.ort}} </span>
            </div>
        </div>
        <div  class=" offset-1 offset-sm-0 order-4 order-sm-2 order-xl-4 order-xxl-2 col-11 col-sm-3 col-xl-4 col-xxl-3 ">
            <div class="form-underline ">
            <div class="form-underline-titel">PLZ</div>
            <span class="form-underline-content">{{data.plz}} </span>
            </div>
        </div>

        <div v-if="withZustelladresse" class="order-5 offset-1 col-11">
        <div class="form-underline ">
        <div class="form-underline-titel">Zustelladresse</div>
            <div class="ms-2 form-check ">
                <input class="form-check-input" type="checkbox"  @click.prevent :checked="data.zustelladresse"  >
               
            </div>

        </div>
        </div>
    </div>
    `

};