const BASE_URL = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
const APPROVE_ANRECHNUNG_DETAIL_URI = "lehre/anrechnung/ApproveAnrechnungDetail";

// TABULATOR FUNCTIONS
// ---------------------------------------------------------------------------------------------------------------------
// Returns relative height (depending on screen size)
function func_height(table){
    return $(window).height() * 0.50;
}

// Adds column details
function func_tableBuilt(table) {
    table.addColumn(
        {
            title: "Details",
            align: "center",
            width: 100,
            formatter: "link",
            formatterParams:{
                label:"Details",
                url:function(cell){
                    return  BASE_URL + "/" + APPROVE_ANRECHNUNG_DETAIL_URI + "?anrechnung_id=" + cell.getData().anrechnung_id
                },
                target:"_blank"
            }
        }, false, "status"  // place column after status
    );
}

// Formats null values to '-'
var format_nullToMinus = function(cell, formatterParams){
    return (cell.getValue() == null) ? '-' : cell.getValue();
}


$(function(){

});