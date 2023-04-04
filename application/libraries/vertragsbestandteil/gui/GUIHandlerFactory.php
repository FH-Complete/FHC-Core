<?php

require_once __DIR__ .'/GUIVertragsbestandteilStunden.php';
require_once __DIR__ .'/GUIVertragsbestandteilFunktion.php';
require_once __DIR__ .'/GUIVertragsbestandteilKuendigungsfrist.php';
require_once __DIR__ .'/GUIVertragsbestandteilZeitaufzeichnung.php';
require_once __DIR__ .'/GUIVertragsbestandteilZusatzvereinbarung.php';

class GUIHandlerFactory {

    public static function getGUIHandler($type)
    {
        switch ($type) {
            case GUIVertragsbestandteilStunden::TYPE_STRING:
                return new GUIVertragsbestandteilStunden();
                break;
            case GUIVertragsbestandteilFunktion::TYPE_STRING:
                return new GUIVertragsbestandteilFunktion();
                break;
            case GUIVertragsbestandteilKuendigungsfrist::TYPE_STRING:
                return new GUIVertragsbestandteilKuendigungsfrist();
                break;
            case GUIVertragsbestandteilZeitaufzeichnung::TYPE_STRING:
                return new GUIVertragsbestandteilZeitaufzeichnung();
                break;
            case GUIVertragsbestandteilZusatzvereinbarung::TYPE_STRING:
                return new GUIVertragsbestandteilZusatzvereinbarung();
                break;
            default:
                
                break;
        }

        throw new \Exception('type not found: '.$type);
    }
}