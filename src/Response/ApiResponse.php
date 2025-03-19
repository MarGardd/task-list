<?php

namespace App\Response;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class ApiResponse
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function create(
        mixed  $data,
        string $message = null,
        int    $status = 200,
        array  $groups = [],
        array  $headers = [],
        bool   $json = false,
    ): JsonResponse
    {
        $formattedData = $this->formatData($data, $groups);
        $responseData = $message
            ? ['data' => $formattedData, 'message' => $message]
            : $formattedData;

        return new JsonResponse($responseData, $status, $headers, $json);
    }

    private function formatData($data, array $groups = []): array
    {
        return $data instanceof PaginationInterface
            ? [
                'total' => $data->getTotalItemCount(),
                'page' => $data->getCurrentPageNumber(),
                'lastPage' => $data->getPageCount(),
                'items' => $this->serializer->normalize($data->getItems(), 'json', ['groups' => $groups]),
            ]
            : $this->serializer->normalize($data, 'json', ['groups' => $groups]);
    }
}
