<?php

namespace App\Resolver;

use App\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver as BaseEntityValueResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly BaseEntityValueResolver $resolver,
        private readonly MapEntity               $defaults = new MapEntity(),
    )
    {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $options = $argument->getAttributes(MapEntity::class, ArgumentMetadata::IS_INSTANCEOF);
        $options = ($options[0] ?? $this->defaults)->withDefaults($this->defaults, $argument->getType());
        try {
            return $this->resolver->resolve($request, $argument);
        } catch (NotFoundHttpException $e) {
            $message = $options->message ?? '';
            throw new NotFoundHttpException(!empty($message)
                ? $message
                : sprintf('"%s" object not found by "%s".', $options->class, self::class));
        }
    }
}