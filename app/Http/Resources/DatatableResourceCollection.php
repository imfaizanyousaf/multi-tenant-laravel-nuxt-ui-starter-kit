<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

use function is_array;

class DatatableResourceCollection extends ResourceCollection
{
    /**
     * Constructor that accepts the paginator and the resource class.
     *
     * @param  class-string|null  $resourceClass
     */
    public function __construct(mixed $resource, protected ?string $resourceClass = null)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->resourceClass
            ? $this->resourceClass::collection($this->collection)
            : $this->collection;

        return [
            'data' => $data,
        ];
    }

    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request): JsonResponse
    {
        /** @var JsonResponse $response */
        $response = parent::toResponse($request);

        /** @var array<string, mixed> $data */
        $data = $response->getData(true);

        // Remove top-level links
        unset($data['links']);

        // Remove links inside meta if present
        if (isset($data['meta']) && is_array($data['meta']) && isset($data['meta']['links'])) {
            unset($data['meta']['links']);
        }

        $response->setData($data);

        return $response;
    }
}
