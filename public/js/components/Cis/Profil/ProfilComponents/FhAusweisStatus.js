export default {
    props:{
        data:{
            type:String,
            
        }
    },
    data(){
        return {
         
        }
    },mounted(){
    },
    template: /*html*/`
    <div class="card">
        <div class="card-body">
        <span  >{{$p.t('profil','fhAusweisStatus',[data])}}</span>
        </div>
    </div>`,
}