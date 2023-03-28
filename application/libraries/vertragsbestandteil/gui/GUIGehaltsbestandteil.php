<?php


require_once __DIR__ . "/AbstractBestandteil.php";
require_once __DIR__ . "/GUIGueltigkeit.php";

/**
 * {
 *   "type": "gehaltsbestandteil",
 *   "guioptions": {
 *     "id": "66246b54-9a42-43e8-b6d3-a541688ebb6e",
 *     "removeable": true
 *   },
 *   "data": {
 *     "gehaltstyp": "zulage",
 *     "betrag": "100",
 *     "gueltigkeit": {
 *       "guioptions": {
 *         "sharedstatemode": "reflect"
 *       },
 *       "data": {
 *         "gueltig_ab": "1.1.2011",
 *         "gueltig_bis": ""
 *       }
 *     },
 *     "valorisierung": ""
 * }
 */
class GUIGehaltsbestandteil extends AbstractBestandteil {

    const TYPE_STRING = "gehaltsbestandteil";

    public function __construct()
    {
        $this->type = GUIVertragsbestandteilStunden::TYPE_STRING;
        $this-> guioptions = ["id" => null, "infos" => [], "errors" => [], "removeable" => true];
        $this->data = [ "gehaltstyp" => "",
                        "betrag" => "",
                        "gueltigkeit" => [
                            "guioptions" => ["sharedstatemode" => "reflect"],
                            "data" =>       ["gueltig_ab"      => "", "gueltig_bis" => ""]
                        ],
                        "valorisierung" => true
                      ];
    }

    public function getTypeString(): string
    {
        return GUIGehaltsbestandteil::TYPE_STRING;
    }

    public function mapJSON(&$decoded)
    {
        //$decoded = json_decode($jsondata);
        $this->checkType($decoded);
        $this->mapGUIOptions($decoded);
        $this->mapData($decoded);
    }

    private function mapGUIOptions(&$decoded)
    {
        $decodedGUIOptions = null;
        if (!$this->getJSONData($decodedGUIOptions, $decoded, 'guioptions'))
        {
            throw new \Exception('missing guioptions');
        }
        $this->getJSONData($this->guioptions, $decodedGUIOptions, 'id');
        $this->getJSONData($this->guioptions, $decodedGUIOptions, 'infos');
        $this->getJSONData($this->guioptions, $decodedGUIOptions, 'errors');
        $this->getJSONData($this->guioptions, $decodedGUIOptions, 'removable');
    }

    private function mapData(&$decoded)
    {
        $decodedData = null;
        if (!$this->getJSONData($decodedData, $decoded, 'data'))
        {
            throw new \Exception('missing data');
        }
        $this->getJSONData($this->data['id'], $decodedData, 'id');
        $this->getJSONData($this->data['gehaltstyp'], $decodedData, 'gehaltstyp');
        $this->getJSONDataInt($this->data['betrag'], $decodedData, 'betrag');
        $gueltigkeit = new GUIGueltigkeit();
        $gueltigkeit->mapJSON($decodedData['gueltigkeit']);
        $this->data['gueltigkeit'] = $gueltigkeit;
        $this->getJSONData($this->data['valorisierung'], $decodedData, 'valorisierung');
    }

}