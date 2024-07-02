export default {
    getAddonLink(addon, lehrveranstaltung_id, studiensemester_kurzbz) {
          return this.$fhcApi.get(
              FHC_JS_DATA_STORAGE_OBJECT.app_root +
              `/addons/${addon}/cis/testapi.php`,
              { lehrveranstaltung_id: lehrveranstaltung_id,
                studiensemester_kurzbz: studiensemester_kurzbz }
          );
      },
  }