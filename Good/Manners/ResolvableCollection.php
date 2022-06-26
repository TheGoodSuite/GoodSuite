<?php

namespace Good\Manners;

use Good\Service\Collection;
use Good\Service\Type;

class ResolvableCollection extends Collection
{
    private $modifier;
    private $owner;
    private $fieldNameInOwner;
    private $storage;

    public function __construct(Type $collectedType, CollectionModifierStorable $modifier, Storable $owner, $fieldNameInOwner)
    {
        parent::__construct($collectedType);

        $this->modifier = $modifier;
        $this->owner = $owner;
        $this->fieldNameInOwner = $fieldNameInOwner;
    }

    public function setStorage(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function isResolved()
    {
        return $this->modifier->isResolved();
    }

    public function isExplicitlyResolved()
    {
        return $this->isResolved();
    }

    public function resolve(Resolver $resolver = null)
    {
        if ($this->modifier->isResolved())
        {
            throw new \Exception("Can only resolve unresolved Collections");
        }

        // I want to get rid of getReferencedTypeIfAny, but it's still there
        // and it's saving me time right now.
        // Using typerevisitors, this would still be possible, though more complex
        $collectedReference = $this->getCollectedType()->getReferencedTypeIfAny();

        if ($collectedReference == null && $resolver !== null)
        {
            throw new \Exception("You can only use a resolver on a collection of storables " .
                " (non-storable collections can be resolved, just without a resolver).");
        }

        if ($collectedReference != null
            && $resolver != null
            && $resolver->getRoot()->getType() !== $collectedReference)
        {
            throw new \Exception("Resolver must match the type of the storabe that is in the collection");
        }

        $this->storage->resolveCollection($this, $resolver);

        return $this;
    }

    public function getStorable()
    {
        return $this->owner;
    }

    public function getFieldName()
    {
        return $this->fieldNameInOwner;
    }

    public function resolveWithData(Collection $data)
    {
        $this->clear();

        foreach ($data as $entry)
        {
            $this->add($entry);
        }

        $this->modifier->clean();
        $this->modifier->markResolved();
    }

    public function __debugInfo()
    {
        if ($this->isResolved())
        {
            return array_merge(["isResolved" => true], parent::__debugInfo());
        }
        else
        {
            return ["isResolved" => false];
        }
    }
}

?>
