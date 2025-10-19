<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Http\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use InvalidArgumentException;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\PaginatedResponse;

class PaginatedResource extends JsonResource implements PaginatedResponse
{
    private array $pagination;

    public function __construct(
        private readonly mixed $resourceCollection,
    ) {
        $paginator = $this->resourceCollection->resource;

        if (! $paginator instanceof LengthAwarePaginator) {
            throw new InvalidArgumentException('Resource must be paginated. It must be an instance of Illuminate\Contracts\Pagination\LengthAwarePaginator');
        }

        $this->pagination = [
            'total' => $paginator->total(),
            'count' => $paginator->count(),
            'perPage' => $paginator->perPage(),
            'currentPage' => $paginator->currentPage(),
            'totalPages' => $paginator->lastPage(),
            'hasMorePages' => $paginator->hasMorePages(),
        ];

        parent::__construct($paginator);
    }

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->resourceCollection->collection,
            'pagination' => $this->pagination,
        ];
    }
}
