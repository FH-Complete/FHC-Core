export default {
    props:{
        data:{
            type:String,
        }
    },
    data(){
        return {

        }
    },
    template: `
    <div class="card">
        <div class="card-body">
        <span>Der FH Ausweis ist am <b>{{data}}</b> ausgegeben worden.</span>
        </div>
    </div>`,
}