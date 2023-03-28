<?php

require_once  __DIR__.'/JSONData.php';

abstract class AbstractBestandteil {

    use JSONData;

    /** @var string type of vertragsbestandteil (i.e. vertragsbestandteilstunden) */
    protected $type;
    /**
     * @var object might contain id and some data needed by the GUI (Error-Messages).
     * Contents depend heavily on type of vertragsbestandteil */
    protected $guioptions;
    /** @var object container for the real data */
    protected $data;

    abstract public function getTypeString(): string;
    abstract public function mapJSON(&$decoded);

    /**
     * check type string ('vertragsbestandteilstunden', etc.)
     */
    public function checkType(&$decoded)
    {
        var_dump($decoded['type']);
        if (!isset($decoded['type']) || (isset($decoded['type']) && $decoded['type'] !== $this->getTypeString()))
        {
            throw new \Exception('wrong type string: "'.$decoded['type'].'" should be "'.$this->getTypeString().'"');
        }
    }   

    /**
     * Get the value of type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     */
    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of guioptions
     */
    public function getGuioptions()
    {
        return $this->guioptions;
    }

    /**
     * Set the value of guioptions
     */
    public function setGuioptions($guioptions): self
    {
        $this->guioptions = $guioptions;

        return $this;
    }

    /**
     * Get the value of data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the value of data
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }
}