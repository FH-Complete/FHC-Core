<?php

require_once __DIR__ . "/AbstractGUIVertragsbestandteil.php";
require_once __DIR__ . "/GUIGehaltsbestandteil.php";
require_once __DIR__ . "/GUIGueltigkeit.php";

/**
 * ```
 * "type": "vertragsbestandteilzeitaufzeichnung",
 * "guioptions": {
 *   "id": "484f7166-7792-4cc7-b906-0db09c65bbf4",
 *   "removeable": true
 * },
 * "data": {
 *   "zeitaufzeichnung": true,
 *   "azgrelevant": false,
 *   "homeoffice": true,
 *   "gueltigkeit": {
 *     "guioptions": {
 *       "sharedstatemode": "reflect"
 *     },
 *     "data": {
 *       "gueltig_ab": "1.1.2010",
 *       "gueltig_bis": ""
 *     }
 *   }
 * }
 */
class GUIVertragsbestandteilZeitaufzeichnung extends AbstractGUIVertragsbestandteil implements JsonSerializable
{    
    const TYPE_STRING = "vertragsbestandteilzeitaufzeichnung";

    public function __construct()
    {
        $this->type = GUIVertragsbestandteilZeitaufzeichnung::TYPE_STRING;
        $this->hasGBS = false;
        $this-> guioptions = ["id" => null, "infos" => [], "errors" => [], "removeable" => true];
        $this->data = null;
    }

    public function getTypeString(): string
    {
        return GUIVertragsbestandteilZeitaufzeichnung::TYPE_STRING;
    }

    /**
     * parse JSON into object
     * @param string $jsondata
     */
    public function mapJSON(&$decoded)
    {
        $this->checkType($decoded);
        $this->mapGUIOptions($decoded);
        $this->mapData($decoded);
    }

    /**
     * ["id" => null, 
     *  "infos" => [], 
     *  "errors" => [], 
     *  "removeable" => true
     * ]
     * @param mixed $decoded decoded JSON data (use associative array)
     */
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
        $this->getJSONDataBool($this->guioptions, $decodedGUIOptions, 'removable');
    }

    private function mapData(&$decoded)
    {
        $decodedData = null;
        if (!$this->getJSONData($decodedData, $decoded, 'data'))
        {
            throw new \Exception('missing data');
        }
        $this->getJSONDataBool($this->data['zeitaufzeichnung'], $decodedData, 'zeitaufzeichnung');
        $this->getJSONDataBool($this->data['azgrelevant'], $decodedData, 'azgrelevant');
        $this->getJSONDataBool($this->data['homeoffice'], $decodedData, 'homeoffice');
        $gueltigkeit = new GUIGueltigkeit();
        $gueltigkeit->mapJSON($decodedData['gueltigkeit']);
        $this->data['gueltigkeit'] = $gueltigkeit;
    }

    public function generateVertragsbestandteil($id) {
        // TODO
    }

    public function jsonSerialize() {
        return [
            "type" => $this->type,
            "guioptions" => $this->guioptions,
            "data" => $this->data];
    }
}