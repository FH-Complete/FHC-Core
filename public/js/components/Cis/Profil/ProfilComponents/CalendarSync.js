export default {
	name: "CalendarSync",
	props: { uid: String, calendarSyncUrls: Array },
	data() {
		return {
			syncInstructionsUrlWithoutParam:
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				"cms/content.php?content_id=",
		};
	},
	methods: {
		copyUrlToClipboard(url) {
			navigator.clipboard.writeText(url);
			this.$fhcAlert.alertSuccess(
				this.$p.t("profil/calendar_sync_clipboard_copy_confirmation"),
			);
		},
	},
	template: `
<div class="card">
    <div class="card-header">
        {{ $p.t("profil/calendar_sync") }}
    </div>
    <div class="card-body">
        <div class="d-flex flex-column gap-3">
            <span>
                <a
                    target="_blank"
                    :href="syncInstructionsUrlWithoutParam + $p.t('DMS-Link/lvplanSyncFAQ')"
                    class="fhc-link-color"
                >
                    {{ $p.t("profil/calendar_sync_instructions") }}
                </a>
                <i class="fa-solid fa-up-right-from-square ms-2"></i>
            </span>
            <ul>
                <li v-for="syncUrl in $props.calendarSyncUrls" :key="syncUrl.identifier">
                    <a 
                        @click.prevent="copyUrlToClipboard(syncUrl.url)"
                        @contextmenu.prevent="copyUrlToClipboard(syncUrl.url)"
                        href="#"
                        class="fhc-link-color"
                    >
                        {{ $p.t(syncUrl.labelPhrase) }}
                    </a>
                    <i class="fa-regular fa-copy ms-2"></i>
                </li>
            </ul>
        </div>
    </div>
</div>`,
};
