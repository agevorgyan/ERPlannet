<?php

declare(strict_types=1);

namespace Tests\Feature\Phase5;

use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Product;
use App\Domain\CRM\Models\Customer;
use App\Domain\IAM\Models\User;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\XmlImport;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class XmlImportCenterTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Branch $branch;

    protected Warehouse $warehouse;

    protected Product $product;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);

        $this->branch = Branch::where('tenant_id', $this->tenant->id)->firstOrFail();
        $this->warehouse = Warehouse::where('tenant_id', $this->tenant->id)->firstOrFail();
        $this->product = Product::where('tenant_id', $this->tenant->id)->firstOrFail();
        $this->customer = Customer::where('tenant_id', $this->tenant->id)->firstOrFail();
    }

    public function test_blocks_malicious_xxe_entity_payloads(): void
    {
        $maliciousXml = '<?xml version="1.0"?>
        <!DOCTYPE foo [
          <!ELEMENT foo ANY >
          <!ENTITY xxe SYSTEM "file:///etc/passwd" >]>
        <orders>
            <order>
                <external_reference>&xxe;</external_reference>
            </order>
        </orders>';

        $file = UploadedFile::fake()->createWithContent('malicious.xml', $maliciousXml);

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson('/api/v1/sales/xml-imports/preview', [
                'xml_file' => $file,
                'branch_id' => $this->branch->id,
                'warehouse_id' => $this->warehouse->id,
            ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('XXE security violation', $response->json('message'));
    }

    public function test_can_preview_erp_xml_format_with_dry_run_validation(): void
    {
        $sku = $this->product->sku;
        $xmlContent = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <ErpOrders>
            <Order>
                <DocumentNumber>XML-ORD-1001</DocumentNumber>
                <CustomerTaxId>{$this->customer->tax_id}</CustomerTaxId>
                <CustomerName>{$this->customer->name}</CustomerName>
                <FulfillmentMethod>delivery</FulfillmentMethod>
                <DeliveryFee>500</DeliveryFee>
                <Notes>XML Import Test</Notes>
                <Lines>
                    <Line>
                        <ProductSku>{$sku}</ProductSku>
                        <Quantity>3</Quantity>
                        <UnitPrice>1500</UnitPrice>
                        <TaxRate>20</TaxRate>
                    </Line>
                </Lines>
            </Order>
        </ErpOrders>";

        $file = UploadedFile::fake()->createWithContent('erp_orders.xml', $xmlContent);

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson('/api/v1/sales/xml-imports/preview', [
                'xml_file' => $file,
                'branch_id' => $this->branch->id,
                'warehouse_id' => $this->warehouse->id,
            ]);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals('erp_xml', $data['format']);
        $this->assertEquals(1, $data['orders_count']);
        $this->assertEquals(5000.0, (float) $data['total_amount']); // (3 * 1500) + 500 delivery = 5000
        $this->assertNotEmpty($data['checksum']);
    }

    public function test_can_preview_armenian_e_invoicing_xml_format(): void
    {
        $sku = $this->product->sku;
        $xmlContent = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <ElectronicInvoice>
            <InvoiceNumber>INV-ARM-2026-001</InvoiceNumber>
            <BuyerName>{$this->customer->name}</BuyerName>
            <BuyerTIN>{$this->customer->tax_id}</BuyerTIN>
            <InvoiceDate>2026-10-10</InvoiceDate>
            <GoodsList>
                <Item>
                    <ProductCode>{$sku}</ProductCode>
                    <Description>Test Armenian Invoiced Item</Description>
                    <Unit>հատ</Unit>
                    <Quantity>2</Quantity>
                    <Price>2000</Price>
                    <TotalAmount>4000</TotalAmount>
                </Item>
            </GoodsList>
        </ElectronicInvoice>";

        $file = UploadedFile::fake()->createWithContent('armenian_invoice.xml', $xmlContent);

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson('/api/v1/sales/xml-imports/preview', [
                'xml_file' => $file,
                'branch_id' => $this->branch->id,
                'warehouse_id' => $this->warehouse->id,
            ]);

        $response->assertStatus(200);
        $this->assertEquals('armenian_e_invoicing', $response->json('data.format'));
        $this->assertEquals(1, $response->json('data.orders_count'));
        $this->assertEquals(4000.0, (float) $response->json('data.total_amount'));
    }

    public function test_confirms_xml_import_transactionally_and_creates_orders(): void
    {
        $sku = $this->product->sku;
        $xmlContent = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <ErpOrders>
            <Order>
                <DocumentNumber>XML-CONFIRM-9901</DocumentNumber>
                <CustomerTaxId>{$this->customer->tax_id}</CustomerTaxId>
                <CustomerName>{$this->customer->name}</CustomerName>
                <Lines>
                    <Line>
                        <ProductSku>{$sku}</ProductSku>
                        <Quantity>2</Quantity>
                        <UnitPrice>3500</UnitPrice>
                    </Line>
                </Lines>
            </Order>
        </ErpOrders>";

        $file = UploadedFile::fake()->createWithContent('orders_to_commit.xml', $xmlContent);

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson('/api/v1/sales/xml-imports/confirm', [
                'xml_file' => $file,
                'branch_id' => $this->branch->id,
                'warehouse_id' => $this->warehouse->id,
            ]);

        $response->assertStatus(201);
        $importRecord = XmlImport::find($response->json('data.id'));
        $this->assertNotNull($importRecord);
        $this->assertEquals('completed', $importRecord->status);
        $this->assertEquals(1, $importRecord->successful_orders);

        // Verify order created in database
        $order = Order::where('external_reference', 'XML-CONFIRM-9901')->first();
        $this->assertNotNull($order);
        $this->assertEquals('xml_import', $order->source);
        $this->assertEquals(7000.0, (float) $order->total);
        $this->assertEquals(2, $order->items->first()->quantity);

        // Submitting same file again should be rejected as duplicate
        $dupResponse = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson('/api/v1/sales/xml-imports/confirm', [
                'xml_file' => $file,
                'branch_id' => $this->branch->id,
                'warehouse_id' => $this->warehouse->id,
            ]);

        $dupResponse->assertStatus(422);
        $this->assertStringContainsString('Duplicate', $dupResponse->json('message'));
    }

    public function test_can_import_official_armenian_taxservice_einvoicing_xml_as_order(): void
    {
        $taxServiceXml = '<?xml version="1.0" encoding="UTF-8"?>
<ExportedAccDocData xmlns="http://www.taxservice.am/tp3/invoice/definitions">
  <SignedAccDocData>
    <Data>
      <SignableData xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Version="1.0" xsi:type="AccountingDocument">
        <User>khohanots24</User>
        <Type>1</Type>
        <Traceable>false</Traceable>
        <GeneralInfo>
          <InvoiceNumber>
            <Number>6065486130</Number>
            <Series>B</Series>
          </InvoiceNumber>
          <DeliveryDate>2026-10-09+04:00</DeliveryDate>
          <Procedure>1</Procedure>
          <AdjustmentAccount>false</AdjustmentAccount>
        </GeneralInfo>
        <SupplierInfo>
          <Taxpayer>
            <TIN>08285927</TIN>
            <Name>«ԳԱՅԱՆԵԻ ԽՈՀԱՆՈՑ» Սահմանափակ պատասխանատվությամբ ընկերություն (ՍՊԸ)</Name>
            <Address>ԵՐԵՎԱՆ ԴԱՎԹԱՇԵՆ ԴԱՎԹԱՇԵՆ ԹԱՂԱՄԱՍ ԴԱՎԹԱՇԵՆ 1 ԹՂՄ. 38 7 ԲՆ.</Address>
            <BankAccount>
              <BankName>«ԱՄԵՐԻԱԲԱՆԿ» ՓԲԸ</BankName>
              <BankAccountNumber>1570077654886500</BankAccountNumber>
            </BankAccount>
          </Taxpayer>
          <SupplyLocation>Հայաստան, ԵՐԵՎԱՆ, ԴԱՎԹԱՇԵՆ, ԴԱՎԹԱՇԵՆ ԹԱՂԱՄԱՍ, 2րդ թաղ 37 շենքի դիմաց</SupplyLocation>
          <SourceIsCar>false</SourceIsCar>
        </SupplierInfo>
        <BuyerInfo>
          <VATNumber>08293072/1</VATNumber>
          <Taxpayer>
            <TIN>08293072</TIN>
            <Name>«ՍԵՅԼՍ ՊԼՅՈՒՍ» Սահմանափակ պատասխանատվությամբ ընկերություն (ՍՊԸ)</Name>
            <Address>ԵՐԵՎԱՆ ԴԱՎԹԱՇԵՆ ԴԱՎԹԱՇԵՆ 1 ԹՂՄ. 38 7 ԲՆ.</Address>
            <TinNotRequired>false</TinNotRequired>
            <IsNatural>false</IsNatural>
          </Taxpayer>
          <DeliveryLocation>Հայաստան, ԵՐԵՎԱՆ, ԴԱՎԹԱՇԵՆ, ԴԱՎԹԱՇԵՆ ԹԱՂԱՄԱՍ, 2-րդ թաղ 37շենքի դիմաց</DeliveryLocation>
        </BuyerInfo>
        <GoodsInfo>
          <Good>
            <Description>- Պելմենի Սիբիրյան (450գր)</Description>
            <ClassifierCode>1902</ClassifierCode>
            <Unit>հատ</Unit>
            <Amount>1</Amount>
            <PricePerUnit>1450</PricePerUnit>
            <Price>1160</Price>
            <Discount>20</Discount>
            <DealType>SPECIAL_TAX_SYSTEM</DealType>
            <TotalPrice>1160</TotalPrice>
          </Good>
          <Good>
            <Description>- Պելմենի Գունավոր (450գր)</Description>
            <ClassifierCode>1902</ClassifierCode>
            <Unit>հատ</Unit>
            <Amount>2</Amount>
            <PricePerUnit>1800</PricePerUnit>
            <Price>2880</Price>
            <Discount>20</Discount>
            <DealType>SPECIAL_TAX_SYSTEM</DealType>
            <TotalPrice>2880</TotalPrice>
          </Good>
          <Good>
            <Description>- Կոտլետ հավի (5 հատ)</Description>
            <ClassifierCode>1602</ClassifierCode>
            <Unit>հատ</Unit>
            <Amount>2</Amount>
            <PricePerUnit>1550</PricePerUnit>
            <Price>2480</Price>
            <Discount>20</Discount>
            <DealType>SPECIAL_TAX_SYSTEM</DealType>
            <TotalPrice>2480</TotalPrice>
          </Good>
          <Good>
            <Description>- Նրբաբլիթ տավարի մսով (6 հատ)</Description>
            <ClassifierCode>1602</ClassifierCode>
            <Unit>հատ</Unit>
            <Amount>5</Amount>
            <PricePerUnit>2400</PricePerUnit>
            <Price>9600</Price>
            <Discount>20</Discount>
            <DealType>SPECIAL_TAX_SYSTEM</DealType>
            <TotalPrice>9600</TotalPrice>
          </Good>
          <Good>
            <Description>- Իշլի քյուֆթա (6 հատ)</Description>
            <ClassifierCode>1602</ClassifierCode>
            <Unit>հատ</Unit>
            <Amount>4</Amount>
            <PricePerUnit>3000</PricePerUnit>
            <Price>9600</Price>
            <Discount>20</Discount>
            <DealType>SPECIAL_TAX_SYSTEM</DealType>
            <TotalPrice>9600</TotalPrice>
          </Good>
          <Good>
            <Description>- Նագեթս հավի կրծքամսով (300գր)</Description>
            <ClassifierCode>1602</ClassifierCode>
            <Unit>հատ</Unit>
            <Amount>1</Amount>
            <PricePerUnit>1400</PricePerUnit>
            <Price>1120</Price>
            <Discount>20</Discount>
            <DealType>SPECIAL_TAX_SYSTEM</DealType>
            <TotalPrice>1120</TotalPrice>
          </Good>
          <Good>
            <Description>- Կիևյան կոտլետ (4 հատ)</Description>
            <ClassifierCode>1602</ClassifierCode>
            <Unit>հատ</Unit>
            <Amount>3</Amount>
            <PricePerUnit>1600</PricePerUnit>
            <Price>3840</Price>
            <Discount>20</Discount>
            <DealType>SPECIAL_TAX_SYSTEM</DealType>
            <TotalPrice>3840</TotalPrice>
          </Good>
          <Total>
            <TotalPrice>30680</TotalPrice>
          </Total>
        </GoodsInfo>
      </SignableData>
    </Data>
    <Signature>
      <SignatureType>PKCS7</SignatureType>
    </Signature>
    <AccDocMetadata>
      <SubmissionDate>2026-10-09T15:37:20.787341+04:00</SubmissionDate>
    </AccDocMetadata>
  </SignedAccDocData>
</ExportedAccDocData>';

        $file = UploadedFile::fake()->createWithContent('official_taxservice_invoice.xml', $taxServiceXml);

        // 1. Preview (Dry-Run)
        $previewResponse = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson('/api/v1/sales/xml-imports/preview', [
                'xml_file' => $file,
                'branch_id' => $this->branch->id,
                'warehouse_id' => $this->warehouse->id,
            ]);

        $previewResponse->assertStatus(200);
        $previewData = $previewResponse->json('data');

        $this->assertEquals('armenian_e_invoicing', $previewData['format']);
        $this->assertEquals('B6065486130', $previewData['external_document_number']);
        $this->assertEquals(30680.0, (float) $previewData['total_amount']);
        $this->assertEquals(7, count($previewData['preview_payload']['items']));
        $this->assertEquals('08293072', $previewData['preview_payload']['customer']['tax_id']);

        // 2. Confirm and Execute Order Creation
        $confirmResponse = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson('/api/v1/sales/xml-imports/confirm', [
                'import_id' => $previewData['id'],
            ]);

        $confirmResponse->assertStatus(201);

        // 3. Verify Order in Database
        $order = Order::where('external_reference', 'B6065486130')->first();
        $this->assertNotNull($order);
        $this->assertEquals('xml_import', $order->source);
        $this->assertEquals(30680.0, (float) $order->total);
        $this->assertEquals(7, $order->items()->count());

        // Verify Customer Association
        $customer = $order->customer;
        $this->assertNotNull($customer);
        $this->assertEquals('08293072', $customer->tax_id);
        $this->assertStringContainsString('ՍԵՅԼՍ ՊԼՅՈՒՍ', $customer->display_name);

        // Verify Products Auto-Created with Armenian names and HS codes
        $firstItem = $order->items()->where('product_name', 'Պելմենի Սիբիրյան (450գր)')->first();
        $this->assertNotNull($firstItem);
        $this->assertEquals(1, (float) $firstItem->quantity);
        $this->assertEquals(1450.0, (float) $firstItem->unit_price);
        $this->assertEquals(290.0, (float) $firstItem->discount);
        $this->assertEquals(1160.0, (float) $firstItem->total);
    }

    public function test_can_import_official_armenian_taxservice_tax_invoice_xml_with_vat_as_order(): void
    {
        $taxInvoiceXml = '<?xml version="1.0" encoding="UTF-8"?>
<ExportedData xmlns="http://www.taxservice.am/tp3/invoice/definitions">
  <SignedData>
    <Data>
      <SignableData xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Version="1.0" xsi:type="Invoice">
        <Type>1</Type>
        <Traceable>false</Traceable>
        <User>salesplus25</User>
        <GeneralInfo>
          <InvoiceNumber>
            <Number>8485180273</Number>
            <Series>A</Series>
          </InvoiceNumber>
          <SupplyDate>2026-10-09+04:00</SupplyDate>
          <Procedure>2</Procedure>
          <AdjustmentAccount>false</AdjustmentAccount>
        </GeneralInfo>
        <SupplierInfo>
          <VATNumber>08293072/1</VATNumber>
          <Taxpayer>
            <TIN>08293072</TIN>
            <Name>«ՍԵՅԼՍ ՊԼՅՈՒՍ» Սահմանափակ պատասխանատվությամբ ընկերություն (ՍՊԸ)</Name>
            <Address>ԵՐԵՎԱՆ ԴԱՎԹԱՇԵՆ ԴԱՎԹԱՇԵՆ 1 ԹՂՄ. 38 7 ԲՆ.</Address>
            <BankAccount>
              <BankName>«ՀԱՅԷԿՈՆՈՄԲԱՆԿ» ԲԲԸ</BankName>
              <BankAccountNumber>163638058624</BankAccountNumber>
            </BankAccount>
          </Taxpayer>
          <SupplyLocation>Հայաստան, ԵՐԵՎԱՆ, ԴԱՎԹԱՇԵՆ, ԴԱՎԹԱՇԵՆ ԹԱՂԱՄԱՍ, 1-ին թաղ 38շ 7բն</SupplyLocation>
          <SourceIsCar>false</SourceIsCar>
        </SupplierInfo>
        <OnBehalfOfSupplierInfo>
          <Taxpayer>
            <PrincipalTinNotRequired>false</PrincipalTinNotRequired>
          </Taxpayer>
        </OnBehalfOfSupplierInfo>
        <BuyerInfo>
          <VATNumber>00492515/1</VATNumber>
          <Taxpayer>
            <TIN>00492515</TIN>
            <Name>«ԱՆՏԱՌԱՅԻՆ ՍՔԱՅ» Սահմանափակ պատասխանատվությամբ ընկերություն (ՍՊԸ)</Name>
            <Address>ԵՐԵՎԱՆ ՆՈՐՔ-ՄԱՐԱՇ ՆՈՐՔ-ՄԱՐԱՇ ԹԱՂԱՄԱՍ Լ.ԱԶԳԱԼԴՅԱՆ Փ. 4 38 ՏԱՐԱԾՔ</Address>
            <TinNotRequired>false</TinNotRequired>
            <IsNatural>false</IsNatural>
          </Taxpayer>
          <DeliveryLocation>Հայաստան, ԵՐԵՎԱՆ, ԿԵՆՏՐՈՆ, ԿԵՆՏՐՈՆ ԹԱՂԱՄԱՍ, ԼԵՈՆԻԴ ԱԶԳԱԼԴՅԱՆ Փ, 4</DeliveryLocation>
        </BuyerInfo>
        <GoodsInfo>
          <Good>
            <Description>- Պելմենի Գունավոր (450գր)</Description>
            <ClassifierCode>1902</ClassifierCode>
            <Unit>տուփ</Unit>
            <Amount>2</Amount>
            <PricePerUnit>1500</PricePerUnit>
            <Price>3000</Price>
            <VATRate>20.0</VATRate>
            <VAT>600</VAT>
            <TotalPrice>3600</TotalPrice>
          </Good>
          <Good>
            <Description>- Պելմենի Սիբիրյան (450գր)</Description>
            <ClassifierCode>1902</ClassifierCode>
            <Unit>տուփ</Unit>
            <Amount>1</Amount>
            <PricePerUnit>1208.33333</PricePerUnit>
            <Price>1208.33</Price>
            <VATRate>20.0</VATRate>
            <VAT>241.67</VAT>
            <TotalPrice>1450</TotalPrice>
          </Good>
          <Good>
            <Description>- Նրբաբլիթ տավարի մսով (6 հատ)</Description>
            <ClassifierCode>1602</ClassifierCode>
            <Unit>տուփ</Unit>
            <Amount>5</Amount>
            <PricePerUnit>2000</PricePerUnit>
            <Price>10000</Price>
            <VATRate>20.0</VATRate>
            <VAT>2000</VAT>
            <TotalPrice>12000</TotalPrice>
          </Good>
          <Good>
            <Description>- Իշլի քյուֆթա (6 հատ)</Description>
            <ClassifierCode>1602</ClassifierCode>
            <Unit>տուփ</Unit>
            <Amount>4</Amount>
            <PricePerUnit>2500</PricePerUnit>
            <Price>10000</Price>
            <VATRate>20.0</VATRate>
            <VAT>2000</VAT>
            <TotalPrice>12000</TotalPrice>
          </Good>
          <Good>
            <Description>- Հավի կոտլետ (5 հատ)</Description>
            <ClassifierCode>1602</ClassifierCode>
            <Unit>տուփ</Unit>
            <Amount>2</Amount>
            <PricePerUnit>1291.66667</PricePerUnit>
            <Price>2583.33</Price>
            <VATRate>20.0</VATRate>
            <VAT>516.67</VAT>
            <TotalPrice>3100</TotalPrice>
          </Good>
          <Good>
            <Description>- Կիևյան կոտլետ (4 հատ)</Description>
            <ClassifierCode>1602</ClassifierCode>
            <Unit>տուփ</Unit>
            <Amount>3</Amount>
            <PricePerUnit>1333.33333</PricePerUnit>
            <Price>4000</Price>
            <VATRate>20.0</VATRate>
            <VAT>800</VAT>
            <TotalPrice>4800</TotalPrice>
          </Good>
          <Good>
            <Description>- Նագեթս հավի կրծքամսով (300գր)</Description>
            <ClassifierCode>1602</ClassifierCode>
            <Unit>տուփ</Unit>
            <Amount>1</Amount>
            <PricePerUnit>1166.66667</PricePerUnit>
            <Price>1166.67</Price>
            <VATRate>20.0</VATRate>
            <VAT>233.33</VAT>
            <TotalPrice>1400</TotalPrice>
          </Good>
          <Total>
            <Price>31958.33</Price>
            <VAT>6391.67</VAT>
            <TotalPrice>38350</TotalPrice>
          </Total>
        </GoodsInfo>
      </SignableData>
    </Data>
    <Signature>
      <SignatureType>PKCS7</SignatureType>
    </Signature>
    <Signature>
      <SignatureType>PKCS7</SignatureType>
    </Signature>
    <InvoiceMetadata>
      <SubmissionDate>2026-10-09T15:56:56.123933+04:00</SubmissionDate>
      <CoSignDate>2026-10-10T09:29:37.685861+04:00</CoSignDate>
    </InvoiceMetadata>
  </SignedData>
</ExportedData>';

        $file = UploadedFile::fake()->createWithContent('tax_invoice_A8485180273.xml', $taxInvoiceXml);

        // 1. Preview
        $previewResponse = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson('/api/v1/sales/xml-imports/preview', [
                'xml_file' => $file,
                'branch_id' => $this->branch->id,
                'warehouse_id' => $this->warehouse->id,
            ]);

        $previewResponse->assertStatus(200);
        $previewData = $previewResponse->json('data');

        $this->assertEquals('armenian_e_invoicing', $previewData['format']);
        $this->assertEquals('tax_invoice', $previewData['document_type']);
        $this->assertEquals('A8485180273', $previewData['external_document_number']);
        $this->assertEquals('2026-10-09', date('Y-m-d', strtotime($previewData['external_document_date'])));
        $this->assertEquals(38350.0, (float) $previewData['total_amount']);
        $this->assertEquals(7, count($previewData['preview_payload']['items']));
        $this->assertEquals('00492515', $previewData['preview_payload']['customer']['tax_id']);

        // 2. Confirm and Execute Order Creation
        $confirmResponse = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson('/api/v1/sales/xml-imports/confirm', [
                'import_id' => $previewData['id'],
            ]);

        $confirmResponse->assertStatus(201);

        // 3. Verify Order in Database
        $order = Order::where('external_reference', 'A8485180273')->first();
        $this->assertNotNull($order);
        $this->assertEquals('xml_import', $order->source);
        $this->assertEquals(38350.0, (float) $order->total);
        $this->assertEquals(6391.67, (float) $order->tax);
        $this->assertEquals(7, $order->items()->count());

        // Verify Customer
        $customer = $order->customer;
        $this->assertNotNull($customer);
        $this->assertEquals('00492515', $customer->tax_id);
        $this->assertStringContainsString('ԱՆՏԱՌԱՅԻՆ ՍՔԱՅ', $customer->display_name);

        // Verify First Item (gross 1800 with 20% VAT = 3600 total)
        $firstItem = $order->items()->where('product_name', 'Պելմենի Գունավոր (450գր)')->first();
        $this->assertNotNull($firstItem);
        $this->assertEquals(2, (float) $firstItem->quantity);
        $this->assertEquals(1800.0, (float) $firstItem->unit_price);
        $this->assertEquals(20.0, (float) $firstItem->tax_rate);
        $this->assertEquals(600.0, (float) $firstItem->tax_amount);
        $this->assertEquals(3600.0, (float) $firstItem->total);
    }
}
