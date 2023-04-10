<?php

require_once  __DIR__.'/JSONData.php';

/**
 * 
 */
class GUIGueltigkeit implements JsonSerializable {

    use JSONData;

    /** @var array */
    protected $guioptions;
    /** @var array */
    protected $data;

    /**
     * ```
     * "gueltigkeit": {
     *   "guioptions": {
     *     "sharedstatemode": "reflect"
     *   },
     *   "data": {
     *     "gueltig_ab": "",
     *     "gueltig_bis": ""
     *   }```
     */
    public function mapJSON(&$decoded)
    {
        $this->mapGuioptions($decoded);
        $this->mapData($decoded);
    }

    private function mapGuioptions(&$decoded)
    {
        $decodedData = null;
        if (!$this->getJSONData($decodedData, $decoded, 'guioptions'))
        {
            throw new \Exception('missing guioptions');
        }
        $this->guioptions = $decodedData;
    }

    private function mapData(&$decoded)
    {
        $decodedData = null;
        if (!$this->getJSONData($decodedData, $decoded, 'data'))
        {
            throw new \Exception('missing data');
        }
        $this->getJSONData($this->data['gueltig_ab'], $decodedData, 'gueltig_ab');
        $this->getJSONData($this->data['gueltig_bis'], $decodedData, 'gueltig_bis');
        
    }

    /**
     * Get the value of guioptions
     */
    public function getGuioptions()
    {
        return $this->guioptions;
    }

    /**
     * Get the value of data
     */
    public function getData()
    {
        return $this->data;
    }

    public function jsonSerialize() {
        return ["guioptions" => $this->guioptions,
            "data" => $this->data];
    }
}