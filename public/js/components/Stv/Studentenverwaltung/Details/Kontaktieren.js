import { splitMailsHelper } from "../../../../helpers/EmailHelpers.js";
import { splitMailLinks } from "../../../../helpers/EmailHelpers.js";
import FormInput from "../../../Form/Input.js";
import FormForm from "../../../Form/Form.js";

export default {
	name: "Kontaktieren",
	components: {
		FormInput,
		FormForm
	},
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
			mailLinksClicked: [],
			showMailDialog: false,
			showDivKomp: false,
			showDivKompLocalStorageId: 'studvw-contact-showDivKomp'
		}
	},
	activated() {
		const showDivCompLocalStorage = window.localStorage.getItem(this.showDivKompLocalStorageId);
		this.showDivKomp = (showDivCompLocalStorage === 'true') ? true : false;
	},
	methods: {
		internMail(event) {
			if (this.internMails.length)
			{
				splitMailsHelper(this.internMails, event, null, null, this.$fhcAlert, this.$p, this.authUid)
			}
		},
		privateMail(event) {
			if (this.privateMails.length)
			{
				splitMailsHelper(this.privateMails, event, null, null, this.$fhcAlert, this.$p, this.authUid)
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
			this.mailLinksClicked = [];

			this.showMailDialog = true;
		},
		handleMailLink(link, index){
			this.mailLinksClicked.push(index);
			window.open(link, '_blank', 'noopener');
		},
		reset(){
			this.showMailDialog = false;
		},
		onSwitchChange(){
			window.localStorage.setItem(this.showDivKompLocalStorageId, this.showDivKomp);
		}
	},
	template: `
	<div class="core-kontaktieren">
		<div id="elementID"></div>

		<div class="d-flex justify-content-between align-items-start pb-3">
			<a
				v-if="showMailDialog"
				class="btn btn-outline-secondary m-2"
				@click.prevent="reset"
				>
					<span class="fa-solid fa-delete-left" :title="this.$p.t('ui','zurueck')" ></span>
					&nbsp;
					<span>{{ $p.t('ui','zurueck') }}</span>
			</a>
			<div v-else>&nbsp;</div>

			<form-input
				container-class="form-switch mb-0"
				type="checkbox"
				:label="$p.t('ui/kompatibilityMode_WebmailClient')"
				v-model="showDivKomp"
				@change="onSwitchChange"
				>
			</form-input>
		</div>

		<div  v-if="!showDivKomp">
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
		</div>

		<div v-if="showDivKomp">

			<div v-if="!showMailDialog" class="row">
				<div class="col-lg-2">
					<button class="btn btn-secondary mb-2" @click="createMailLinks($event, 'intern')" :title="$p.t('stv', 'bccEMail')">
						<i class="fa-solid fa-mail"></i> {{$p.t('stv', 'internEMail')}} ({{$p.t('ui', 'webmailclient')}})
					</button>
				</div>
			</div>
			<div v-if="!showMailDialog" class="row">
				<div class="col-lg-2">
					<button class="btn btn-secondary mb-2" @click="createMailLinks($event, 'private')" :title="$p.t('stv', 'bccEMail')">
						<i class="fa-solid fa-mail"></i> {{$p.t('stv', 'privateEMail')}} ({{$p.t('ui', 'webmailclient')}})
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
								@click.prevent="handleMailLink(link, index)"
								>
									Email {{ index + 1 }}
							</a>
							<span v-show="mailLinksClicked.includes(index)">&nbsp;<i class="fa-solid fa-check text-success"></i></span>
						</div>
					</div>
					<div v-else>
							<a
								:href="mailLinks[0]"
								@click.prevent="handleMailLink(mailLinks[0], index)"
								class="btn btn-primary m-2"
								>
									{{$p.t('ui', 'openInMailClient')}}
							</a>
					</div>
			</div>

		</div>
	</div>
	`
};
