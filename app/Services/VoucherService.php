<?php

namespace App\Services;

use App\Events\Vouchers\VouchersCreated;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Log;
use SimpleXMLElement;

class VoucherService
{
    public function getVouchers(int $page, int $paginate): LengthAwarePaginator
    {
        return Voucher::with(['lines', 'user'])->paginate(perPage: $paginate, page: $page);
    }

    /**
     * @param string[] $xmlContents
     * @param User $user
     * @return Voucher[]
     */
    public function storeVouchersFromXmlContents(array $xmlContents, User $user): array
    {
        $vouchers = [];
        foreach ($xmlContents as $xmlContent) {
            $vouchers[] = $this->storeVoucherFromXmlContent($xmlContent, $user);
        }

        VouchersCreated::dispatch($vouchers, $user);

        return $vouchers;
    }

    public function storeVoucherFromXmlContent(string $xmlContent, User $user): Voucher
    {
        $xml = new SimpleXMLElement($xmlContent);

        $id = (string) $xml->xpath('//cbc:ID')[0];
        $voucherType = (string) $xml->xpath('//cbc:InvoiceTypeCode')[0];
        $currency = (string) $xml->xpath('//cbc:DocumentCurrencyCode')[0];

        $issuerName = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name')[0];
        $issuerDocumentType = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
        $issuerDocumentNumber = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

        $receiverName = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName')[0];
        $receiverDocumentType = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
        $receiverDocumentNumber = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

        $totalAmount = (string) $xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount')[0];

        $informationFromId = $this->extractInformationFromId($id);
        $voucher = new Voucher([
            'serie' => $informationFromId['serie'],
            'number' => $informationFromId['number'],
            'type' => $this->humanizeVoucherType($voucherType),
            'currency' => $currency,
            'issuer_name' => $issuerName,
            'issuer_document_type' => $issuerDocumentType,
            'issuer_document_number' => $issuerDocumentNumber,
            'receiver_name' => $receiverName,
            'receiver_document_type' => $receiverDocumentType,
            'receiver_document_number' => $receiverDocumentNumber,
            'total_amount' => $totalAmount,
            'xml_content' => $xmlContent,
            'user_id' => $user->id,
        ]);

        $voucher->save();

        foreach ($xml->xpath('//cac:InvoiceLine') as $invoiceLine) {
            $name = (string) $invoiceLine->xpath('cac:Item/cbc:Description')[0];
            $quantity = (float) $invoiceLine->xpath('cbc:InvoicedQuantity')[0];
            $unitPrice = (float) $invoiceLine->xpath('cac:Price/cbc:PriceAmount')[0];

            $voucherLine = new VoucherLine([
                'name' => $name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'voucher_id' => $voucher->id,
            ]);

            $voucherLine->save();
        }


        return $voucher;
    }

    private function extractInformationFromId(string $id): array
    {
        $information = explode('-', $id);

        return [
            "serie" => $information[0],
            "number" => $information[1]
        ];
    }

    private function humanizeVoucherType(string $code)
    {
        $descriptions = [
            "01" => "FACTURA"
        ];

        if (!array_key_exists($code, $descriptions)) {
            throw new \Exception('codigo de tipo de comprobante no se encuentra registrado');
        }

        return $descriptions[$code];
    }
}
