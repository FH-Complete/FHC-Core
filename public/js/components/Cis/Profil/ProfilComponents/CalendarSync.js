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
            <a
                target="_blank"
                :href="syncInstructionsUrlWithoutParam + $p.t('DMS-Link/lvplanSyncFAQ')"
                class="fhc-link-color d-flex flex-row gap-2 align-items-center"
            >
                <span>
                    <i class="fa-solid fa-up-right-from-square ms-2"></i>
                </span>
                <span>
                    {{ $p.t("profil/calendar_sync_instructions") }}
                </span>
            </a>
            <a
                v-for="syncUrl in $props.calendarSyncUrls"
                :key="syncUrl.identifier"
                @click.prevent="copyUrlToClipboard(syncUrl.url)"
                @contextmenu.prevent="copyUrlToClipboard(syncUrl.url)"
                href="#"
                class="fhc-link-color d-flex flex-row gap-2 align-items-center"
            >
                <span>
                    <i class="fa-regular fa-copy ms-2 text-decoration-none"></i>
                </span>
                    {{ $p.t(syncUrl.labelPhrase) }}
            </a>
        </div>
    </div>
</div>`,
};
