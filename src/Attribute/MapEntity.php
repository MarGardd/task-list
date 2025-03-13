<?php

namespace App\Attribute;

use Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver;
use Symfony\Bridge\Doctrine\Attribute\MapEntity as ParentMapEntity;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class MapEntity extends ParentMapEntity
{
    public function __construct(
        ?string           $class = null,
        ?string           $objectManager = null,
        ?string           $expr = null,
        ?array            $mapping = null,
        ?array            $exclude = null,
        ?bool             $stripNull = null,
        array|string|null $id = null,
        ?bool             $evictCache = null,
        bool              $disabled = false,
        string            $resolver = EntityValueResolver::class,
        public string     $message = 'Entity not found',
        public bool       $belongsCurrentUser = true,
    )
    {
        parent::__construct(disabled: $disabled, resolver: $resolver);
        $this->class = $class;
        $this->objectManager = $objectManager;
        $this->expr = $expr;
        $this->mapping = $mapping;
        $this->exclude = $exclude;
        $this->stripNull = $stripNull;
        $this->id = $id;
        $this->evictCache = $evictCache;
    }
}