(function() {
  var callWithJQuery;

  callWithJQuery = function(pivotModule) {
    if (typeof exports === "object" && typeof module === "object") {
      return pivotModule(require("jquery"));
    } else if (typeof define === "function" && define.amd) {
      return define(["jquery"], pivotModule);
    } else {
      return pivotModule(jQuery);
    }
  };
  callWithJQuery(function($) {
    var deFmt, deFmtInt, deFmtPct, nf, tpl;
    nf = $.pivotUtilities.numberFormat;
    tpl = $.pivotUtilities.aggregatorTemplates;
    deFmt = nf({
      thousandsSep: ".",
      decimalSep: ","
    });
    deFmtInt = nf({
      digitsAfterDecimal: 0,
      thousandsSep: ".",
      decimalSep: ","
    });
    deFmtPct = nf({
      digitsAfterDecimal: 1,
      scaler: 100,
      suffix: "%",
      thousandsSep: ".",
      decimalSep: ","
    });
    return $.pivotUtilities.locales.de = {
      localeStrings: {
				renderError: "Bei dem Zeichnen der Pivot Ergebnisse ist ein Fehler aufgetreten.",
				computeError: "Bei dem berechnen der Pivot Ergebnisse ist ein Fehler aufgetreten.",
				uiRenderError: "Bei dem Zeichnen des Pivot Interfaces ist ein Fehler aufgetreten.",
				selectAll: "W&auml;hle alle",
				selectNone: "W&auml;hle keine",
				tooMany: "(zu viele Ergebnisse)",
				filterResults: "Ergebnisse filtern",
				totals: "Total",
				vs: "vs",
				by: "von"
      },
      aggregators: {
				"Anzahl": tpl.count(deFmtInt),
				"Anzahl einzigartiger Werte": tpl.countUnique(deFmtInt),
				"Liste einzigartiger Werte": tpl.listUnique(", "),
				"Summe": tpl.sum(deFmt),
				"Summe in ganzen Zahlen": tpl.sum(deFmtInt),
				"Durchschnitt": tpl.average(deFmt),
				"Summe &uuml;ber Summe": tpl.sumOverSum(deFmt),
				"80% obere Grenze": tpl.sumOverSumBound80(true, deFmt),
				"80% untere Grenze": tpl.sumOverSumBound80(false, deFmt),
				"Prozent": tpl.fractionOf(tpl.sum(), "total", deFmtPct),
				"Prozent pro Reihe": tpl.fractionOf(tpl.sum(), "row", deFmtPct),
				"Prozent pro Spalte": tpl.fractionOf(tpl.sum(), "col", deFmtPct),
				"Anzahl als Teil des Ganzen": tpl.fractionOf(tpl.count(), "total", deFmtPct),
				"Anzahl als Teil der Reihe": tpl.fractionOf(tpl.count(), "row", deFmtPct),
				"Anzahl als Teil der Spalte": tpl.fractionOf(tpl.count(), "col", deFmtPct)
      },
      renderers: {
        "Tabelle": $.pivotUtilities.renderers["Table"],
        "Tabelle mit Balken": $.pivotUtilities.renderers["Table Barchart"],
        "Heatmap": $.pivotUtilities.renderers["Heatmap"],
        "Heatmap f&uuml;r Reihen": $.pivotUtilities.renderers["Row Heatmap"],
        "Heatmap f&uuml;r Spalten": $.pivotUtilities.renderers["Col Heatmap"]
      }
    };
  });

}).call(this);


//# sourceMappingURL=pivot.de.js.map
