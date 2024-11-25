export default {
    
    open: function () {
        return this.$fhcApi.get(
        `/api/frontend/v1/Ampeln/open`,{});
    },

    all: function () {
        return this.$fhcApi.get(
        `/api/frontend/v1/Ampeln/all`,{});
    },

    confirm: function (ampel_id) {
        return this.$fhcApi.get(
        `/api/frontend/v1/Ampeln/confirm/${ampel_id}`,{});
    },

}