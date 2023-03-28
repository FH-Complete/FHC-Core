<?php

require_once __DIR__ . "/AbstractGUIVertragsbestandteil.php";
require_once __DIR__ . "/GUIGehaltsbestandteil.php";
require_once __DIR__ . "/GUIGueltigkeit.php";

class GUIVertragsbestandteilFunktion extends AbstractGUIVertragsbestandteil  implements JsonSerializable
{    
    const TYPE_STRING = "vertragsbestandteilfunktion";

    public function __construct()
    {
        $this->type = GUIVertragsbestandteilFunktion::TYPE_STRING;
        $this->hasGBS = true;
        $this-> guioptions = ["id" => null, "infos" => [], "errors" => [], "removeable" => false];
        $this->data = ["funktion" => "Leitung",
                       "orget" => "",
                       "gueltigkeit" => [
                           "guioptions" => ["sharedstatemode" => "reflect"],
                           "data" =>       ["gueltig_ab"      => "", "gueltig_bis" => ""]
                       ]
                      ];
        $this->gbs = [];
    }

    public function getTypeString(): string
    {
        return GUIVertragsbestandteilFunktion::TYPE_STRING;
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
        $this->mapGBS($decoded);
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

    /**
     * {
     *  "funktion": "Leitung",
     *    "orget": "sdf",
     *    "gueltigkeit": {
     *      "guioptions": {
     *        "sharedstatemode": "reflect"
     *      },
     *      "data": {
     *        "gueltig_ab": "",
     *        "gueltig_bis": ""
     *      }
     *    }
     * }
     */
    private function mapData(&$decoded)
    {
        $decodedData = null;
        if (!$this->getJSONData($decodedData, $decoded, 'data'))
        {
            throw new \Exception('missing data');
        }
        $this->getJSONData($this->data['funktion'], $decodedData, 'funktion');
        $this->getJSONData($this->data['orget'], $decodedData, 'orget');
        $gueltigkeit = new GUIGueltigkeit();
        $gueltigkeit->mapJSON($decodedData['gueltigkeit']);
        $this->data['gueltigkeit'] = $gueltigkeit;
    }

    private function mapGBS(&$decoded)
    {
        //echo "gbs: ";var_dump($decoded);
        $decodedGbsList = [];
        if (!$this->getJSONData($decodedGbsList, $decoded, 'gbs'))
        {
            throw new \Exception('missing gbs');
        }
        $guiGBS = null;
        foreach ($decodedGbsList as $decodedGbs) {
            $guiGBS = new GUIGehaltsbestandteil();
            $guiGBS->mapJSON($decodedGbs);
            $this->gbs[] = $guiGBS;
        }
    }


    public function generateVertragsbestandteil($id) {
        // TODO
    }

    public function jsonSerialize() {
        return [
            "type" => $this->type,
            "guioptions" => $this->guioptions,
            "data" => $this->data,
            "gbs" => $this->gbs];
    }

}