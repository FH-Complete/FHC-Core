export default {

    props:{
        title:{
            type:String,
            
        },
        data:{
            type:Object,
        }
    },
    data(){
        return {
            FotoSperre:this.data.foto_sperre,
        }
    },
    
    methods:{
        
        sperre_foto_function() {
            //TODO: make this better
            if (!this.data) {
              return;
            }
            Vue.$fhcapi.UserData.sperre_foto_function(!this.FotoSperre).then((res) => {
              this.FotoSperre = res.data.foto_sperre;
            
            });
          },
    },
    computed:{
        get_image_base64_src: function() {
            if (!this.data.foto) {
              return "";
            }
            return "data:image/jpeg;base64," + this.data.foto;
          },
        name: function(){
            return {vorname:this.data.Vorname, nachname: this.data.Nachname};
        },
        profilInfo: function(){
            let res = {};
            let notIncludedProperties=["Vorname", "Nachname", "foto_sperre","foto"];
            Object.keys(this.data).forEach((key)=>{
                if(!notIncludedProperties.includes(key)){
                    res[key] = this.data[key];
                }
            })
            return res;
        }
    },
    template: `
    
    <div class="card h-100">
    <div class="card-header">
    {{title}}
    </div>
    <div class="card-body">
    
     



<div  class="gy-3 row justify-content-center align-items-center">




<!-- SQUEEZING THE IMAGE INSIDE THE FIRST INFORMATION COLUMN -->
<!-- START OF THE FIRST ROW WITH THE PROFIL IMAGE -->
<div class="col-12 col-sm-6 mb-2">
<div class="row justify-content-center">
      <div class="col-auto " style="position:relative">
        <img class=" img-thumbnail " style=" max-height:150px; "  :src="get_image_base64_src"></img>
        <!-- LOCKING IMAGE FUNCTIONALITY -->
        
        
        <div role="button" @click.prevent="sperre_foto_function" class="image-lock" >
        <i :class="{'fa':true, ...(FotoSperre?{'fa-lock':true}:{'fa-lock-open':true})} " ></i>
        </div>


      </div>
    </div>
  <!-- END OF THE ROW WITH THE IMAGE -->
  </div>
<!-- END OF SQUEEZE -->



<!-- COLUMNS WITH MULTIPLE ROWS NEXT TO PROFIL PICTURE -->
<div class="col-12 col-sm-6">
<div class="row gy-4">
<div class="col-12">

      
<div class="form-underline ">
<div class="form-underline-titel">Vorname</div>
<span class="form-underline-content">{{name.vorname}} </span>
</div>



</div>
<div class="col-12">

<div class="form-underline ">
<div class="form-underline-titel">Nachname</div>
<span class="form-underline-content">{{name.nachname}} </span>
</div>

</div>
</div>


</div>








<div v-for="(wert,bez) in profilInfo" class="col-md-6 col-sm-12 ">


<div class="form-underline ">
<div class="form-underline-titel">{{bez}}</div>
<span class="form-underline-content">{{wert?wert:'-'}} </span>
</div>



</div>


    </div>


    
    </div>
  </div>`,

};