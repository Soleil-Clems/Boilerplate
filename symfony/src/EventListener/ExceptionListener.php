<?php

namespace App\EventListener;

use App\Exception\ValidationException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $status = 500;
        $data = [
            'success' => false,
            'message' => $exception->getMessage(),
        ];

        if ($exception instanceof ValidationException) {
            $data['message'] = $exception->getErrors();
            $status = 400;
        }

        elseif ($exception instanceof ExceptionInterface) {
            $data['message'] = 'Erreur de désérialisation JSON';
            $data['details'] = $exception->getMessage();
            $status = 400;
        }
        elseif ($exception instanceof UnauthorizedHttpException) {
            $data = [
                'success' => false,
                'message' => 'Vous devez vous authentifier pour accéder à cette ressource.',
            ];
            $status = 401;
        }
        elseif($exception instanceof UnprocessableEntityHttpException){
            $rawMessage = $exception->getMessage();
            if (str_contains($rawMessage, "\n")) {
                $lines = explode("\n", $rawMessage);
                $errors = [];
                foreach ($lines as $line) {
                    if (str_contains($line, ':')) {
                        [$field, $message] = explode(':', $line, 2);
                        $errors[trim($field)] = trim($message);
                    } else {
                        $errors[] = $line;
                    }
                }
                $data['message'] = $errors;
                $status = 422;
            }
        }

        elseif ($exception instanceof AuthenticationException ) {
            $data['message'] = 'Vous devez vous authentifier pour accéder à cette ressource.';
            $status = 401;
        }

        elseif ($exception instanceof AccessDeniedException) {
            $data['message'] = 'Vous n’avez pas les droits pour effectuer cette action.';
            $status = 403;
        }

        elseif ($exception instanceof NotFoundHttpException) {
            $data['message'] = 'Ressource non trouvée.';
            $status = 404;
        }

        elseif ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            $data['message'] = $exception->getMessage();
        }

        else {

            $rawMessage = $exception->getMessage();
            if (str_contains($rawMessage, "\n")) {
                $lines = explode("\n", $rawMessage);
                $errors = [];
                foreach ($lines as $line) {
                    if (str_contains($line, ':')) {
                        [$field, $message] = explode(':', $line, 2);
                        $errors[trim($field)] = trim($message);
                    } else {
                        $errors[] = $line;
                    }
                }
                $data['message'] = $errors;
            }
        }

        $event->setResponse(new JsonResponse($data, $status));
    }
}
