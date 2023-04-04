<?php

require_once __DIR__ . "/AbstractBestandteil.php";
require_once __DIR__ . "/GUIGueltigkeit.php";

class FormData extends AbstractBestandteil {

    const TYPE_STRING = "formdata";

    /** @var array GUI data */
    protected $children;
    /** @var array */
    protected $vbs = [];   

    public function getTypeString(): string
    {
        return FormData::TYPE_STRING;
    }

    /**
     * read JSON and turn it into data structure
     */
    public function mapJSON(&$decoded)
    {
        $this->checkType($decoded);
        // preserve gui data
        $this->mapChildren($decoded);
        // data contains DV
        $this->mapData($decoded);
        // vbs array
        $this->mapVbs($decoded);
    }

    public function generateJSON()
    {
        $json = json_encode([
            "children" => $this->children,
            "data" => $this->generateDvJSON(),
            "vbs" => $this->generateVbsJSON()
        ]);
        return $json;
    }

    private function mapChildren(&$decoded)
    {
        $decodedData = null;
        if (!$this->getJSONData($decodedData, $decoded, 'children'))
        {
            throw new \Exception('missing children');
        }
        $this->getJSONData($this->data['children'], $decodedData, 'children');
    }

    private function mapData(&$decoded)
    {
        $decodedData = null;
        if (!$this->getJSONData($decodedData, $decoded, 'data'))
        {
            throw new \Exception('missing data');
        }
        
        $this->getJSONDataInt($this->data['dienstverhaeltnisid'], $decodedData, 'dienstverhaeltnisid');
        $this->getJSONData($this->data['unternehmen'], $decodedData, 'unternehmen');
        $this->getJSONData($this->data['vertragsart_kurzbz'], $decodedData, 'vertragsart_kurzbz');
        $gueltigkeit = new GUIGueltigkeit();
        $gueltigkeit->mapJSON($decodedData['gueltigkeit']);
        $this->data['gueltigkeit'] = $gueltigkeit;
        //$this->getJSONData($this->data['gueltigkeit'], $decodedData, 'gueltigkeit');
    }

    private function generateDvJSON()
    {
        return json_encode($this->data);
    }


    private function mapVbs(&$decoded)
    {
        if (!$this->getJSONData($this->vbs, $decoded, 'vbs'))
        {
            throw new \Exception('missing vbs');
        }
        //$this->getJSONData($this->vbs, $decodedData, 'vbs');
    }

    private function generateVbsJSON()
    {
        return json_encode($this->vbs);
    }

    /**
     * Get the value of children
     */
    public function getChildren()
    {
        return $this->children;
    }


    /**
     * Get the value of vbs
     */
    public function getVbs()
    {
        return $this->vbs;
    }


}