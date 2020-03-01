<?php

namespace Good\Memory;

use Good\Manners\Storable;
use Good\Manners\StorableVisitor;

class FieldDenamifier implements StorableVisitor
{
    private $storage;
    private $data;
    private $out;

    public function __construct($storage)
    {
        $this->storage = $storage;
    }

    public function denamifyFields(array $data, Storable $storable)
    {
        $this->data = $data;
        $this->out = array();

        $storable->acceptStorableVisitor($this);

        return $this->out;
    }

    private function visitProperty($name, $makeArray)
    {
        $fieldNamified = $this->storage->fieldNamify($name);

        if (array_key_exists($fieldNamified, $this->data))
        {
            $value = $this->data[$fieldNamified];

            if ($makeArray)
            {
                $value = [$value];
            }

            $this->out[$name] = $value;
        }
    }

    public function visitReferenceProperty($name, $datatypeName, $dirty,
                                                    Storable $value = null)
    {
        $this->visitProperty($name, false);
    }

    public function visitTextProperty($name, $dirty, $value)
    {
        $this->visitProperty($name, false);
    }

    public function visitIntProperty($name, $dirty, $value)
    {
        $this->visitProperty($name, false);
    }

    public function visitFloatProperty($name, $dirty, $value)
    {
        $this->visitProperty($name, false);
    }

    public function visitDatetimeProperty($name, $dirty, $value)
    {
        $this->visitProperty($name, false);
    }

    public function visitCollectionProperty($name, $value, $modifier)
    {
        $this->visitProperty($name, true);
    }
}
