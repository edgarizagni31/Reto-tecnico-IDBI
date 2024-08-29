<?php

namespace App\Services;

use App\Events\Vouchers\VouchersCreated;
use App\Events\Vouchers\VouchersNotCreated;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Log;
use SimpleXMLElement;
use Storage;
use Str;

class VoucherService
{
    public function getVouchers(int $page, int $paginate): LengthAwarePaginator
    {
        return Voucher::with(['lines', 'user'])->paginate(perPage: $paginate, page: $page);
    }

    public function getVoucher(array $values): Collection
    {
        $queryBuilder = Voucher::query();

        extract($values);

        if ($serie) {
            $queryBuilder->where('serie', $serie);
        }

        if ($number) {
            $queryBuilder->where('number', $number);
        }

        if ($startDate) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $queryBuilder->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
            $queryBuilder->whereDate('created_at', '<=', $endDate);
        }


        return $queryBuilder->get();
    }

    /**
     * @param string[] $xmlContents
     * @param User $user
     * @return Voucher[]
     */
    public function storeVouchersFromXmlContents(array $xmlContents, User $user): array
    {
        $vouchers = [];
        $wrongVouchers = [];
        foreach ($xmlContents as $xmlContent) {
            try {
                $vouchers[] = $this->storeVoucherFromXmlContent($xmlContent, $user);
            } catch (\Exception $exception) {
                $wrongVouchers[] = $this->reportWrongVoucher($xmlContent, $exception->getMessage());
            }
        }

        VouchersCreated::dispatch($vouchers, $user);
        VouchersNotCreated::dispatch($wrongVouchers, $user);

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

        $this->validateStructure($information);
        $this->validateSerie($information[0]);
        $this->validateNumber($information[1]);

        return [
            "serie" => $information[0],
            "number" => $information[1]
        ];
    }

    private function validateStructure(array $information): void
    {
        if (count($information) != 2) {
            throw new \Exception('Estructura del ID no es correcta.');
        }
    }

    private function validateSerie(string $serie): void
    {
        if (!str_starts_with($serie, 'F')) {
            throw new \Exception('Serie no es válida.');
        }
    }

    private function validateNumber(string $number): void
    {
        if (intval($number) > 1) {
            throw new \Exception('Número correlativo no es correcto.');
        }
    }

    private function humanizeVoucherType(string $code)
    {
        $descriptions = [
            "01" => "FACTURA"
        ];

        $this->validateCode($code, $descriptions);

        return $descriptions[$code];
    }

    private function validateCode(string $code, array $descriptions): void
    {
        if (!array_key_exists($code, $descriptions)) {
            throw new \Exception('Código no se encuentra registrado.');
        }
    }

    private function reportWrongVoucher(string $xmlContent, string $reason): array
    {
        $filename = substr(Str::uuid()->toString(), 0, 5) . '.xml';
        $absolutePath = '';
        $fileWasCreated = Storage::put($filename, $xmlContent);

        if ($fileWasCreated) {
            $absolutePath = Storage::path($filename);
        }

        return [
            "file" => $absolutePath,
            "filename" => $filename,
            "file_was_created" => $fileWasCreated,
            "reason" => $reason
        ];
    }

    public function deleteVoucher(string $voucherId)
    {
        $voucher = Voucher::find($voucherId);

        if (!$voucher) {
            throw new \Exception("voucher con $voucherId no existe");
        }

        $voucher->delete();

        return $voucher;
    }
}
