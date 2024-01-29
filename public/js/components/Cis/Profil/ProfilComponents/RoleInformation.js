export default {
    data(){
        return {

        }
    },
    props:{
        data:{
            type:Object,
        },
        title:{
            type:String,
        }
    },
    computed:{
        
    },
    created(){
        //TODO: check if data.Telefon is a valid telefon number to call before using it as a tel: link
    },
    template:`
    <div class="card">
                 
    <div class="card-header">
    {{title}}
    </div>
    <div class="card-body">
        <div class="gy-3 row">
        <div v-for="(wert,bez) in data" class="col-md-6 col-sm-12 ">
          
           <div class="form-underline">
           <div class="form-underline-titel">{{bez }}</div>

           <!-- print Telefon link -->
           <a  v-if="bez=='Telefon'" :href="data.Telefon?'tel:'+data.Telefon:''" class="form-underline-content">{{wert?wert:'-'}}</a>
           
           <!-- else print information -->
           <span v-else class="form-underline-content">{{wert?wert:'-'}}</span>
           </div>
           
        </div>

       
        </div>
    
</div>

</div>`
};