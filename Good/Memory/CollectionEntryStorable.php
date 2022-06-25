<?php

namespace Good\Memory;

use Good\Manners\Storable;
use Good\Manners\StorableVisitor;
use Good\Manners\Storage;
use Good\Manners\ValidationToken;
use Good\Rolemodel\Schema\Type;
use Good\Rolemodel\Schema\Type\CollectionType;
use Good\Rolemodel\Schema\Type\DateTimeType;
use Good\Rolemodel\Schema\Type\BooleanType;
use Good\Rolemodel\Schema\Type\FloatType;
use Good\Rolemodel\Schema\Type\IntType;
use Good\Rolemodel\Schema\Type\ReferenceType;
use Good\Rolemodel\Schema\Type\TextType;
use Good\Rolemodel\TypeVisitor;

class CollectionEntryStorable implements Storable, TypeVisitor
{
    public function setNew($value) {}
    public function setStorage(Storage $storage) {}
    public function setValidationToken(ValidationToken $token) {}
    public function getId() {}
    public function setId($value) {}
    public function hasValidId() {}
    public function isDirty() {}
    public function clean() {}
    public function markCollectionsUnresolved() {}
    public function delete() {}
    public function isResolved() { return true; }
    public function isExplicitlyResolved() { return true; }

    private $storableVisitor;

    public function acceptStorableVisitor(StorableVisitor $visitor)
    {
        $visitor->visitReferenceProperty("owner", $this->owner->getType(), true, $this->owner);

        $this->acceptStorableVisitorValueOnly($visitor);
    }

    public function acceptStorableVisitorValueOnly(StorableVisitor $visitor)
    {
        $this->storableVisitor = $visitor;
        $this->collectedType->acceptTypeVisitor($this);
    }

    public function visitCollectionType(CollectionType $type)
    {
        throw new \Exception("Collections of Collection are not supported at the moment.");
    }

    public function  visitDateTimeType(DateTimeType $type)
    {
        $this->storableVisitor->visitDateTimeProperty("value", true, $this->value);
    }

    public function  visitBooleanType(BooleanType $type)
    {
        $this->storableVisitor->visitBooleanProperty("value", true, $this->value);
    }

    public function visitFloatType(FloatType $type)
    {
        $this->storableVisitor->visitFloatProperty("value", true, $this->value);
    }

    public function visitIntType(IntType $type)
    {
        $this->storableVisitor->visitIntProperty("value", true, $this->value);
    }

    public function visitReferenceType(ReferenceType $type)
    {
        $this->storableVisitor->visitReferenceProperty("value", $type->getReferencedType(), true, $this->value);
    }

    public function visitTextType(TextType $type)
    {
        $this->storableVisitor->visitTextProperty("value", true, $this->value);
    }

    public function getType()
    {
        return $this->typeName;
    }

    public function isDeleted()
    {
        return false;
    }

    public function isNew()
    {
        return true;
    }

    private $owner;
    private $value;
    private $collectionFieldName;
    private $typeName;
    private $collectedType;

    public function __construct(Type $collectedType, $typeName)
    {
        $this->collectedType = $collectedType;
        $this->typeName = $typeName;
    }

    public function setOwner(Storable $owner)
    {
        $this->owner = $owner;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setCollectionFieldName($name)
    {
        $this->collectionFieldName = $name;
    }

    public function getCollectionFieldName()
    {
        return $this->collectionFieldName;
    }
}

?>
