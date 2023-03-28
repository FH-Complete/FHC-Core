<?php

require_once __DIR__ . "/AbstractBestandteil.php";

/**
 * Wrapper for Vertragsbestandteil in JSON schema produced by the GUI.
 * Example:
 * ```{ "bb09324f-19f6-41d2-a371-388ef8fdb49e": {
 *      "type": "vertragsbestandteil",
 *      "guioptions": {
 *         "id": "bb09324f-19f6-41d2-a371-388ef8fdb49e",
 *         "infos": [
 *           "test info 1",
 *           "test info 2"
 *         ],
 *         "errors": [
 *           "test error 1",
 *           "test error 2"
 *         ]
 *       },
 *       "data": {
 *          "stunden": "38,5",
 *          "gueltigkeit": {
 *            "guioptions": {
 *              "sharedstatemode": "ignore"
 *            },
 *            "data": {
 *              "gueltig_ab": "1.1.2011",
 *              "gueltig_bis": "31.12.2014"
 *            }
 *          }
 *        },
 *       },
 *       "gbs": [
 *         {
 *           "type": "gehaltsbestandteil",
 *           "guioptions": {
 *             "infos": [
 *               "test info 1",
 *               "test info 2"
 *             ],
 *             "errors": [
 *               "test error 1",
 *               "test error 2"
 *             ]
 *           },
 *           "data": {
 *             "gehaltstyp": "",
 *             "betrag": "3333",
 *             "gueltigkeit": {
 *               "guioptions": {
 *                 "sharedstatemode": "ignore"
 *               },
 *               "data": {
 *                 "gueltig_ab": "1.1.2011",
 *                 "gueltig_bis": "31.12.2014"
 *               }
 *             },
 *             "valorisierung": ""
 *           }
 *         }
 *       ]
 * }```
 */
abstract class AbstractGUIVertragsbestandteil extends AbstractBestandteil
{

    /** @var string hashkey */
    protected $uuid;
    
    /** @var boolean does this vertragsbestandteil have a GBS array? */
    protected $hasGBS = false;
    /** @var array gehaltsbestandteile connected to current vertragsbestandteil */
    protected $gbs;

    /** @var VertragsbestandteilLib */
    protected $vbsLib;
    
    public function __construct()
    {
        $this->vbsLib = new VertragsbestandteilLib();
    }

    abstract public function generateVertragsbestandteil($id);

    /**
     * Get the value of uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set the value of uuid
     */
    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    

    /**
     * Get the value of hasGBS
     */
    public function getHasGBS()
    {
        return $this->hasGBS;
    }

    /**
     * Set the value of hasGBS
     */
    public function setHasGBS($hasGBS): self
    {
        $this->hasGBS = $hasGBS;

        return $this;
    }

  

    /**
     * Get the value of gbs
     */
    public function getGbs()
    {
        return $this->gbs;
    }

    /**
     * Set the value of gbs
     */
    public function setGbs($gbs): self
    {
        $this->gbs = $gbs;

        return $this;
    }
}    