import { splitMailsHelper } from "../../../../helpers/EmailHelpers.js"
export default {
	name: "Kontaktieren",
	computed: {
		internMails() {
			if (this.modelValue.mail_intern)
			{
				return [this.modelValue.mail_intern];
			}
			return this.modelValue.map(e => e.mail_intern);
		},
		privateMails()
		{
			if (this.modelValue.mail_privat)
			{
				return [this.modelValue.mail_privat];
			}
			return this.modelValue.map(e => e.mail_privat);
		},
	},
	props: {
		modelValue: Object
	},

	methods: {
		internMail(event) {
			if (this.internMails.length)
			{
				splitMailsHelper(this.internMails, event, null, this.$fhcAlert, this.$p)
			}
		},
		privateMail(event) {
			if (this.privateMails.length)
			{
				splitMailsHelper(this.privateMails, event, null, this.$fhcAlert, this.$p)
			}
		}
	},
	template: `
	<div>
	<div id="elementID"></div>
		<div class="row">
			<div class="col-lg-2">
				<button class="btn btn-primary mb-2" @click="internMail($event)" :title="$p.t('stv', 'bccEMail')">
					<i class="fa-solid fa-mail"></i> {{$p.t('stv', 'internEMail')}}
				</button>
			</div>
		</div>
		
		<div class="row">
			<div class="col-lg-2">
				<button class="btn btn-primary mb-2" @click="privateMail($event)" :title="$p.t('stv', 'bccEMail')">
					<i class="fa-solid fa-mail"></i> {{$p.t('stv', 'privateEMail')}}
				</button>
			</div>
		</div>
	</div>`
};