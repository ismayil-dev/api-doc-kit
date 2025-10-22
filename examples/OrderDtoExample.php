<?php

/**
 * Example: Using #[DataSchema] for Auto-Generated OpenAPI Schemas
 *
 * This example demonstrates the complete workflow of using DTOs
 * with automatic schema generation.
 */

namespace App\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use IsmayilDev\ApiDocKit\Attributes\Schema\DataSchema;

// Step 1: Define your enum
enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}

// Step 2: Create DTO with #[DataSchema] attribute
#[DataSchema(
    title: 'Order',
    description: 'Order data transfer object with formatted fields'
)]
class OrderDto implements Arrayable
{
    public function __construct(
        public readonly string $id,
        public readonly string $customerName,
        public readonly int $totalCents,
        public readonly OrderStatus $status,
        public readonly \DateTimeImmutable $createdAt,
        public readonly bool $isPaid,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customerName,
            'total_cents' => $this->totalCents,
            'total_formatted' => '$'.number_format($this->totalCents / 100, 2),
            'status' => $this->status->value,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'is_paid' => $this->isPaid,
        ];
    }
}

// Step 3: Use in controller

namespace App\Http\Controllers;

use App\DTOs\OrderDto;
use App\Models\Order;
use IsmayilDev\ApiDocKit\Attributes\Resources\ApiEndpoint;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\SingleResourceResponse;
use IsmayilDev\ApiDocKit\Http\Responses\ResourceResponse;

class OrderController
{
    #[ApiEndpoint(
        entity: Order::class,
        responseEntity: 'OrderDto'  // Reference the DTO schema
    )]
    public function show(Order $order): SingleResourceResponse
    {
        $dto = new OrderDto(
            id: $order->id,
            customerName: $order->customer->name,
            totalCents: $order->total_cents,
            status: OrderStatus::from($order->status),
            createdAt: new \DateTimeImmutable($order->created_at),
            isPaid: $order->paid_at !== null,
        );

        return new ResourceResponse($dto->toArray());
    }

    #[ApiEndpoint(
        entity: Order::class,
        responseEntity: 'OrderDto'
    )]
    public function index(): CollectionResponse
    {
        $orders = Order::with('customer')->get();

        $dtos = $orders->map(fn ($order) => new OrderDto(
            id: $order->id,
            customerName: $order->customer->name,
            totalCents: $order->total_cents,
            status: OrderStatus::from($order->status),
            createdAt: new \DateTimeImmutable($order->created_at),
            isPaid: $order->paid_at !== null,
        ));

        return new ResourceCollection($dtos->map->toArray());
    }
}

/**
 * Generated OpenAPI Schema (automatically):
 *
 * components:
 *   schemas:
 *     OrderDto:
 *       title: Order
 *       description: Order data transfer object with formatted fields
 *       type: object
 *       required:
 *         - id
 *         - customer_name
 *         - total_cents
 *         - total_formatted
 *         - status
 *         - created_at
 *         - is_paid
 *       properties:
 *         id:
 *           type: string
 *           example: "string"
 *         customer_name:
 *           type: string
 *           example: "string"
 *         total_cents:
 *           type: integer
 *           example: 123
 *         total_formatted:
 *           type: string
 *           example: "string"
 *         status:
 *           type: string
 *           example: "string"
 *         created_at:
 *           type: string
 *           example: "string"
 *         is_paid:
 *           type: boolean
 *           example: true
 */
