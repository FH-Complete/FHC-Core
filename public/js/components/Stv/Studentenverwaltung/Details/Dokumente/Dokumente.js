import DocumentsUnaccepted from './List/Unaccepted.js';
import DocumentsAccepted from './List/Accepted.js';

export default {
	components: {
		DocumentsUnaccepted,
		DocumentsAccepted
	},
	props: {
		student: Object
	},
	data(){
		return {
			listMissingDocuments: []
		}
	},
	methods: {
		reloadUnaccepted(){
			this.$refs.tableUnaccepted.reload();
		},
		reloadAccepted(){
			this.$refs.tableAccepted.reload();
		},
	},
	template: `
	 <div class="stv-details-documents h-100 pb-3">
 	
	 	<div class="row mb-3">
	 		<div class="col-6">
	 			 <documents-unaccepted
	 			 	ref="tableUnaccepted"
					:prestudent_id="student.prestudent_id"
					:studiengang_kz="student.studiengang_kz"
					@reloadAccepted="reloadAccepted"
				></documents-unaccepted>
			</div>	 		

			<div class="col-6">
				<documents-accepted
					ref="tableAccepted"
					:prestudent_id="student.prestudent_id"
					:studiengang_kz="student.studiengang_kz"
					@reloadUnaccepted="reloadUnaccepted"
				></documents-accepted>	
			</div>

		</div>

	 </div>
	 `
}
