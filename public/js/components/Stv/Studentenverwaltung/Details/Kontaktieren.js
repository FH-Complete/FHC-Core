import { splitMailLinks } from "../../../../helpers/EmailHelpers.js";

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
	inject: {
		authUid: {
			from: 'authUid',
			required: true
		},
	},
	data(){
		return {
			mailLinks: [],
			showMailDialog: false
		}
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
		},
		createMailLinks(event, mailGroup){
			let recipients = mailGroup == 'private' ? this.privateMails : this.internMails;
			this.mailLinks = splitMailLinks(
				recipients,
				"",
				event.ctrlKey || event.metaKey,
				this.authUid
			);

			this.showMailDialog = true;
		},
		handleMailLink(event){
			window.open(event.target.href, '_blank', 'noopener');
		},
		reset(){
			this.showMailDialog = false;
		}
	},
	template: `
	<div class="core-kontaktieren>
		<div id="elementID"></div>

		<div v-if="!showMailDialog" class="row">
			<div class="col-lg-2">
				<button class="btn btn-primary mb-2" @click="createMailLinks($event, 'intern')" :title="$p.t('stv', 'bccEMail')">
					<i class="fa-solid fa-mail"></i> {{$p.t('stv', 'internEMail')}}
				</button>
			</div>
		</div>

		<div v-if="!showMailDialog" class="row">
			<div class="col-lg-2">
				<button class="btn btn-primary mb-2" @click="createMailLinks($event, 'private')" :title="$p.t('stv', 'bccEMail')">
					<i class="fa-solid fa-mail"></i> {{$p.t('stv', 'privateEMail')}}
				</button>
			</div>
		</div>

		<div v-if="showMailDialog" class="mt-3">
				<div v-if="mailLinks.length > 1" class="mt-2">
					<p>{{$p.t('stv', 'zuvieleEMails')}}</p>

					<div
						v-for="(link,index) in mailLinks"
						:key="index"
						class="mb-2">

						<a
							:href="link"
							@click.prevent="handleMailLink"
							>
								Email {{ index + 1 }}
						</a>
					</div>
						<a
							class="btn btn-outline-secondary m-2"
							@click.prevent="reset"
							>
								<span class="fa-solid fa-rotate-right" :title="this.$p.t('ui','zurueck')" ></span>
						</a>
				</div>
				<div v-else>
						<a
							:href="mailLinks[0]"
							@click.prevent="handleMailLink"
							class="btn btn-primary m-2"
							>
								{{$p.t('ui', 'openInMailClient')}}
						</a>

						<a
							class="btn btn-outline-secondary m-2"
							@click.prevent="reset"
							>
								<span class="fa-solid fa-rotate-right" :title="this.$p.t('ui','zurueck')" ></span>
						</a>
				</div>
		</div>
	`
};