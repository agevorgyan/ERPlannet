<?php

namespace App\Domain\Sales\Services\Xml;

use SimpleXMLElement;

interface XmlFormatAdapterInterface
{
    public function canHandle(SimpleXMLElement $xml): bool;

    public function getFormatName(): string;

    public function detectDocumentType(SimpleXMLElement $xml): string;

    /**
     * @return array{
     *     document_number: string|null,
     *     document_date: string|null,
     *     customer: array{
     *         name: string|null,
     *         tax_id: string|null,
     *         phone: string|null,
     *         email: string|null,
     *         address: string|null
     *     }|null,
     *     items: array<int, array{
     *         sku: string,
     *         name: string|null,
     *         quantity: float,
     *         unit_price: float|null,
     *         discount: float,
     *         tax_rate: float,
     *         notes: string|null
     *     }>,
     *     notes: string|null
     * }
     */
    public function parse(SimpleXMLElement $xml): array;
}
