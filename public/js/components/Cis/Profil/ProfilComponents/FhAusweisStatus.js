export default {
	props: {
		data: {
			type: String,
		},
	},
	data() {
		return {};
	},
	template: /*html*/ `
    <div class="card">
        <div class="card-body">
            <span v-if="data">{{$p.t('profil','fhAusweisStatus',[data])}}</span>
			<span v-else>{{$p.t('profil','fhAusweisStatusKeine')}}</span>
        </div>
    </div>`,
};
