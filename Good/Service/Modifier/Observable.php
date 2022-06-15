<?php

namespace Good\Service\Modifier;

use Good\Rolemodel\Schema;
use Good\Rolemodel\Schema\TypeDefinition;

class Observable implements \Good\Service\Modifier
{
    public function __construct()
    {
    }

    public function implementingInterfaces()
    {
        return array('\\Good\\Service\\Observable');
    }

    public function baseClassConstructor()
    {
        return '';
    }
    public function baseClassBody()
    {
        return '';
    }

    public function varDefinitionAfter(Schema\Member $member) {return '';}
    public function getterBegin(Schema\Member $member) {return '';}
    public function setterBegin(Schema\Member $member) {return '';}
    public function setterEnd(Schema\Member $member)
    {
        $res  = "        \n";
        $res .= '        $this->GSMObservable_notifyObservers();' . "\n";

        return $res;
    }
    public function topOfGetterSwitch(TypeDefinition $typeDefinition) { return ''; }

    public function classBody(TypeDefinition $typeDefinition)
    {
        $res  = "    // Observer pattern (Observable)\n";
        $res .= '    private $observers = array();' . "\n";
        $res .= "    \n";
        $res .= '    public function registerObserver(\\Good\\Service\\Observer $observer)'. "\n";
        $res .= "    {\n";
        $res .= '        $this->observers[] = $observer;' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public function unregisterObserver(\\Good\\Service\\Observer $observer)'. "\n";
        $res .= "    {\n";
        $res .= '        $pos = \\array_search($observer, $this->observers);' . "\n";
        $res .= '        if ($pos !== FALSE)' . "\n";
        $res .= "        {\n";
        $res .= '            \array_splice($this->observers, $pos, 1);' . "\n";
        $res .= "        }\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= "    private function GSMObservable_notifyObservers()\n";
        $res .= "    {\n";
        $res .= '        foreach ($this->observers as $observer)' . "\n";
        $res .= "        {\n";
        $res .= '            $observer->notifyObserver($this);' . "\n";
        $res .= "        }\n";
        $res .= "    }\n";
        $res .= "    \n";

        return $res;
    }

    public function constructor() { return ''; }
    public function extraFiles() {return array();}
    public function beforeClass(Schema\TypeDefinition $typeDefinition) { return ''; }
}

?>
