<?php

declare(strict_types=1);

namespace App\Domain\Integration\Contracts;

use App\Domain\Catalog\Models\Product;
use App\Domain\Integration\Models\TenantIntegration;

interface ECommerceDriverInterface extends IntegrationDriverInterface
{
    /**
     * Push product to the e-commerce platform.
     *
     * @return array{external_id: string, checksum: string, response: array}
     */
    public function pushProduct(TenantIntegration $integration, Product $product, ?string $externalId = null): array;

    /**
     * Update product inventory level on the e-commerce platform.
     *
     * @return array{external_id: string, stock_quantity: float, success: bool}
     */
    public function pushStock(TenantIntegration $integration, Product $product, float $quantity, ?string $externalId = null): array;

    /**
     * Update product price on the e-commerce platform.
     *
     * @return array{external_id: string, price: float, success: bool}
     */
    public function pushPrice(TenantIntegration $integration, Product $product, float $price, ?string $externalId = null): array;

    /**
     * Fetch orders from the e-commerce platform.
     *
     * @param array<string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    public function fetchOrders(TenantIntegration $integration, array $params = []): array;

    /**
     * Push order status update to the e-commerce platform.
     */
    public function updateOrderStatus(TenantIntegration $integration, string $externalOrderId, string $status): bool;

    /**
     * Verify incoming webhook authenticity.
     */
    public function verifyWebhookSignature(string $rawPayload, string $signature, string $secret): bool;
}
