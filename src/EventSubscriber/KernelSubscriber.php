<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class KernelSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onException'
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if($exception instanceof ValidationFailedException || $exception->getPrevious() instanceof ValidationFailedException) {
            $validationFailedException = ($exception instanceof ValidationFailedException)
                ? $exception
                : $exception->getPrevious()
            ;
            $errors = [];
            foreach($validationFailedException->getViolations() as $violation) {
                $errors[] = [
                    'error' => $violation->getMessage()
                ];
            }
            $event->setResponse(new JsonResponse($errors, $exception->getStatusCode()));
        } else if ($exception instanceof NotFoundHttpException || $exception instanceof AccessDeniedHttpException) {
            $errors = [
                'error' => $exception->getMessage()
            ];
            $event->setResponse(new JsonResponse($errors, $exception->getStatusCode()));
        }
    }
}