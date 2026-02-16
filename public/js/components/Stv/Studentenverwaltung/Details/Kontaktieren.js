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
		async splitMails(mails, event) {
			let splititem = ",";
			let maillist = mails.join(splititem);
			let mailto = "";

			if (maillist.length > 2024)
			{
				if (await this.$fhcAlert.confirm({message: this.$p.t('stv', 'zuvieleEMails') }) === false)
					return;
			}

			let firstrun = true;
			let useBcc = event?.ctrlKey || event?.metaKey;
			while (maillist.length > 0)
			{
				if (maillist.length > 2024)
				{
					let splitposition = maillist.lastIndexOf(splititem, 1900);
					mailto = maillist.substring(0, splitposition);
					maillist = maillist.substring(splitposition + 1);
				}
				else
				{
					mailto = maillist;
					maillist = "";
				}

				let mailLink = useBcc ? `mailto:?bcc=${mailto}` : `mailto:${mailto}`;

				if (firstrun)
				{
					window.location.href = mailLink;
					firstrun = false;
				}
				else
				{
					if (await this.$fhcAlert.confirm({message: this.$p.t('stv', 'weitereEMail')}) === true)
					{
						window.location.href = mailLink;
					}
				}

			}
		},
		internMail(event) {
			if (this.internMails.length)
			{
				this.splitMails(this.internMails, event);
			}
		},
		privateMail(event) {
			if (this.privateMails.length)
			{
				this.splitMails(this.privateMails, event);
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