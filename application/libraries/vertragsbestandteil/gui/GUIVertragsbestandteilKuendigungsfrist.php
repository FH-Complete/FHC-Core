<?php

require_once __DIR__ . "/AbstractGUIVertragsbestandteil.php";

/**
  *   "type": "vertragsbestandteilkuendigungsfrist",
  *   "guioptions": {
  *     "id": "c71a803d-b8be-4fbc-82f1-381e1d01df2e",
  *     "removeable": true
  *   },
  *   "data": {
  *     "arbeitgeber_frist": "8",
  *     "arbeitnehmer_frist": "4",
  *     "gueltigkeit": {
  *       "guioptions": {
  *         "sharedstatemode": "reflect"
  *       },
  *       "data": {
  *         "gueltig_ab": "1.1.2011",
  *         "gueltig_bis": ""
  *       }
  *     }
 */
class GUIVertragsbestandteilKuendigungsfrist extends AbstractGUIVertragsbestandteil implements JsonSerializable
{    
    const TYPE_STRING = "vertragsbestandteilkuendigungsfrist";

    public function __construct()
    {
        $this->type = GUIVertragsbestandteilKuendigungsfrist::TYPE_STRING;
        $this->hasGBS = false;
        $this-> guioptions = ["id" => null, "infos" => [], "errors" => [], "removeable" => true];
        $this->data = ["arbeitnehmer_frist" => "",
                       "arbeitgeber_frist" => "",
                       "gueltigkeit" => [
                           "guioptions" => ["sharedstatemode" => "reflect"],
                           "data" =>       ["gueltig_ab"      => "", "gueltig_bis" => ""]
                       ]
                      ];        
    }

    public function getTypeString(): string
    {
        return GUIVertragsbestandteilKuendigungsfrist::TYPE_STRING;
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

    /**    
     */
    private function mapData(&$decoded)
    {
        $decodedData = null;
        if (!$this->getJSONData($decodedData, $decoded, 'data'))
        {
            throw new \Exception('missing data');
        }
        $this->getJSONDataInt($this->data['arbeitnehmer_frist'], $decodedData, 'arbeitnehmer_frist');
        $this->getJSONDataInt($this->data['arbeitgeber_frist'], $decodedData, 'arbeitgeber_frist');
        $this->getJSONData($this->data['gueltigkeit'], $decodedData, 'gueltigkeit');
    }

    private function mapGBS()
    {
        $decodedGbsList = [];
        if (!$this->getJSONData($decodedGbsList, $decoded, 'gbs'))
        {
            throw new \Exception('missing gbs');
        }
        $guiGBS = null;
        foreach ($decodedGbsList as $decodedGbs) {
            $guiGBS = new GUIGehaltsbestandteil();
            $guiGBS->mapJSON($decodedGbs);
        }
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