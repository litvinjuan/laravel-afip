<?php

namespace litvinjuan\LaravelAfip\WebServices;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Arr;
use litvinjuan\LaravelAfip\Enum\AfipConcept;
use litvinjuan\LaravelAfip\Enum\AfipInvoiceLetter;
use litvinjuan\LaravelAfip\Enum\AfipInvoiceType;
use litvinjuan\LaravelAfip\Enum\AfipService;
use litvinjuan\LaravelAfip\Exceptions\AfipException;
use litvinjuan\LaravelAfip\Exceptions\AfipSoapException;

class ElectronicBillingWebService extends WebService
{
    protected function getAfipService(): AfipService
    {
        return AfipService::wsfe;
    }

    /**
     * @throws AfipException
     * @throws AfipSoapException
     */
    private function call(string $name, ?array $params = []): array
    {
        $paramsWithAuth = Arr::add($params, 'Auth', $this->getAuthData());

        $response = $this->request($name, $paramsWithAuth);

        $resultKey = "{$name}Result";
        $result = Arr::get($response, $resultKey);

        if (Arr::has($result, 'Errors')) {
            $this->throwFirstError($result);
        }

        return $result;
    }

    public function getLastInvoiceNumber(AfipInvoiceType $invoiceType, int $pointOfSale): int
    {
        return $this->call('FECompUltimoAutorizado', [
            'PtoVta' => $pointOfSale,
            'CbteTipo' => $invoiceType->value,
        ])['CbteNro'];
    }

    public function getLastInvoice(AfipInvoiceType $invoiceType, int $pointOfSale): ?array
    {
        $lastInvoiceNumber = $this->getLastInvoiceNumber($invoiceType, $pointOfSale);

        if ($lastInvoiceNumber === 0) {
            return null;
        }

        return $this->getInvoice($invoiceType, $pointOfSale, $lastInvoiceNumber);
    }

    public function createInvoice(AfipInvoiceType $invoiceType, int $pointOfSale, array $invoice): array
    {
        return $this->createInvoices($invoiceType, $pointOfSale, [$invoice]);
    }

    public function getInvoice(AfipInvoiceType $invoiceType, int $pointOfSale, int $invoiceNumber): ?array
    {
        try {
            return $this->call('FECompConsultar', [
                'FeCompConsReq' => [
                    'PtoVta' => $pointOfSale,
                    'CbteTipo' => $invoiceType->value,
                    'CbteNro' => $invoiceNumber,
                ],
            ]);
        } catch (AfipException $exception) {
            if ($exception->getCode() === 602) {
                return null;
            }
            throw $exception;
        }
    }

    public function getPointsOfSale(): array
    {
        return $this->call('FEParamGetPtosVenta')['ResultGet']['PtosVenta'];
    }

    public function getActivities(): array
    {
        return $this->call('FEParamGetActividades');
    }

    public function getMaxInvoicesPerRequest(): int
    {
        return $this->call('FECompTotXRequest')['RegXReq'];
    }

    public function getInvoiceTypes(): array
    {
        return $this->call('FEParamGetTiposCbte')['ResultGet']['CbteTipo'];
    }

    public function getConceptTypes(): array
    {
        return $this->call('FEParamGetTiposConcepto')['ResultGet']['ConceptoTipo'];
    }

    public function getDocumentTypes(): array
    {
        return $this->call('FEParamGetTiposDoc')['ResultGet']['DocTipo'];
    }

    public function getIVATypes(): array
    {
        return $this->call('FEParamGetTiposIva')['ResultGet']['IvaTipo'];
    }

    public function getCountries(): array
    {
        return $this->call('FEParamGetTiposPaises')['ResultGet']['PaisTipo'];
    }

    public function getCurrencies(): array
    {
        return $this->call('FEParamGetTiposMonedas')['ResultGet']['Moneda'];
    }

    public function getOptionalTypes(): array
    {
        return $this->call('FEParamGetTiposOpcional')['ResultGet']['OpcionalTipo'];
    }

    public function getTributeTypes(): array
    {
        return $this->call('FEParamGetTiposTributos')['ResultGet']['TributoTipo'];
    }

    public function status(): bool
    {
        $result = $this->call('FEDummy', []);

        return collect($result['FEDummyResult'])
            ->every(function ($value, $key) {
                return $value === 'OK';
            });
    }

    private function getAuthData(): array
    {
        return [
            'Token' => $this->getTokenAuthorization()->getToken(),
            'Sign' => $this->getTokenAuthorization()->getSign(),
            'Cuit' => $this->cuit,
        ];
    }

    protected function getSoapVersioin(): int
    {
        return SOAP_1_1;
    }

    /**
     * @throws AfipException
     */
    private function throwFirstError(array $result): void
    {
        $error = $result['Errors']['Err'];
        throw new AfipException($error['Msg'], $error['Code']);
    }

    public function createInvoices(AfipInvoiceType $invoiceType, int $pointOfSale, array $invoices): array
    {
        $lastInvoiceNumber = $this->getLastInvoiceNumber($invoiceType, $pointOfSale);

        $data = [
            'FeCAEReq' => [
                'FeCabReq' => [
                    'CantReg' => count($invoices),
                    'PtoVta' => $pointOfSale,
                    'CbteTipo' => $invoiceType->value,
                ],
                'FeDetReq' => collect($invoices)->mapWithKeys(function ($invoice, $index) use ($lastInvoiceNumber, $invoiceType) {
                    $number = $lastInvoiceNumber + $index + 1;

                    if ($invoiceType->letter() === AfipInvoiceLetter::C && Arr::has($invoice, 'subtotal')) {
                        $invoice['net_taxed_total'] = Arr::get($invoice, 'subtotal');
                    }

                    return [
                        'FECAEDetRequest' => collect([
                            'Concepto' => $this->getConceptId($invoice),
                            'DocNro' => Arr::get($invoice, 'customer.id', 0),
                            'DocTipo' => Arr::get($invoice, 'customer.id_type', 99),
                            'CbteDesde' => $number,
                            'CbteHasta' => $number,
                            'CbteFch' => $this->formatDate(Arr::get($invoice, 'emission_date')),
                            'FchVtoPago' => $this->formatDate(Arr::get($invoice, 'payment_due_date')),
                            'FchServDesde' => $this->formatDate(Arr::get($invoice, 'period_from_date')),
                            'FchServHasta' => $this->formatDate(Arr::get($invoice, 'period_to_date')),
                            'MonId' => Arr::get($invoice, 'currency.id', 'PES'),
                            'MonCotiz' => Arr::get($invoice, 'currency.exchange_rate', 1),
                            'PeriodoAsoc' => Arr::has($invoice, ['associated_invoice_dates.from_date', 'associated_invoice_dates.to_date']) ? [
                                'FchDesde' => $this->formatDate(Arr::get($invoice, 'associated_invoice_dates.from_data')),
                                'FchHasta' => $this->formatDate(Arr::get($invoice, 'associated_invoice_dates.to_date')),
                            ] : null,

                            'ImpTotConc' => $this->getNetNotTaxedTotal($invoice),
                            'ImpNeto' => $this->getNetTaxedTotal($invoice),
                            'ImpOpEx' => $this->getExemptTotal($invoice),
                            'ImpTrib' => $this->getTaxesTotal($invoice),
                            'ImpIVA' => $this->getIvaTotal($invoice),
                            'ImpTotal' => $this->getTotal($invoice),

                            'CbtesAsoc' => collect(Arr::get($invoice, 'associated_invoices'))->map(function ($associatedInvoice) {
                                return [
                                    'Tipo' => Arr::get($associatedInvoice, 'invoice_type'),
                                    'Nro' => Arr::get($associatedInvoice, 'invoice_number'),
                                    'PtoVta' => Arr::get($associatedInvoice, 'point_of_sale'),
                                    'Cuit' => Arr::get($associatedInvoice, 'merchant_cuit'),
                                    'CbteFch' => $this->formatDate(Arr::get($associatedInvoice, 'emission_date')),
                                ];
                            })->filter()->toArray(),
                            'Tributos' => collect(Arr::get($invoice, 'taxes'))->map(function ($tax) {
                                return [
                                    'Id' => Arr::get($tax, 'id'),
                                    'Desc' => Arr::get($tax, 'description'),
                                    'BaseImp' => Arr::get($tax, 'base'),
                                    'Alic' => Arr::get($tax, 'rate'),
                                    'Importe' => Arr::get($tax, 'total'),
                                ];
                            })->filter()->toArray(),
                            'Iva' => collect(Arr::get($invoice, 'iva'))->map(function ($iva) {
                                return [
                                    'Id' => Arr::get($iva, 'id'),
                                    'BaseImp' => Arr::get($iva, 'base'),
                                    'Importe' => Arr::get($iva, 'total'),
                                ];
                            })->filter()->toArray(),
                            'Opcionales' => collect(Arr::get($invoice, 'optional_fields'))->map(function ($optionalField) {
                                return [
                                    'Id' => Arr::get($optionalField, 'id'),
                                    'Valor' => Arr::get($optionalField, 'value'),
                                ];
                            })->filter()->toArray(),
                            'Compradores' => collect(Arr::get($invoice, 'buyers'))->map(function ($buyer) {
                                return [
                                    'DocNro' => Arr::get($buyer, 'id'),
                                    'DocTipo' => Arr::get($buyer, 'id_type'),
                                    'Porcentaje' => Arr::get($buyer, 'percentage'),
                                ];
                            })->filter()->toArray(),
                            'Actividades' => collect(Arr::get($invoice, 'activities'))->map(function ($activity) {
                                return [
                                    'Id' => Arr::get($activity, 'id'),
                                ];
                            })->filter()->toArray(),
                        ])->reject(function ($value) {
                            return is_null($value);
                        })->reject(function ($value) {
                            return is_array($value) && empty($value);
                        })->toArray(),
                    ];
                })->toArray(),
            ],
        ];

        $response = $this->call('FECAESolicitar', $data);

        $status = Arr::get($response, 'FeDetResp.FECAEDetResponse.Resultado', 'R') !== 'R';
        $cae = Arr::get($response, 'FeDetResp.FECAEDetResponse.CAE');
        $caeExpirationDate = Carbon::parse(Arr::get($response, 'FeDetResp.FECAEDetResponse.CAEFchVto'));

        $errors = $this->getObservations($response);

        return [
            'status' => $status,
            'cae' => $cae,
            'cae_expiration_date' => $caeExpirationDate,
            'errors' => $errors,
        ];
    }

    private function formatDate(string|DateTimeInterface|null $date): ?string
    {
        if (! $date) {
            return null;
        }

        return Carbon::parse($date)->format('Ymd');
    }

    private function getTotal(array $invoice): float
    {
        return $this->getNetNotTaxedTotal($invoice) +
            $this->getExemptTotal($invoice) +
            $this->getNetTaxedTotal($invoice) +
            $this->getTaxesTotal($invoice) +
            $this->getIvaTotal($invoice);
    }

    private function getTaxesTotal(array $invoice): float
    {
        return collect(Arr::get($invoice, 'taxes'))->sum(function ($tax) {
            return Arr::get($tax, 'total', 0);
        });
    }

    private function getIvaTotal(array $invoice): float
    {
        return collect(Arr::get($invoice, 'iva'))->sum(function ($iva) {
            return Arr::get($iva, 'total', 0);
        });
    }

    private function getExemptTotal(array $invoice): float
    {
        return Arr::get($invoice, 'exempt_total', 0);
    }

    private function getNetTaxedTotal(array $invoice): float
    {
        return Arr::get($invoice, 'net_taxed_total', 0);
    }

    private function getNetNotTaxedTotal(array $invoice): float
    {
        return Arr::get($invoice, 'net_not_taxed_total', 0);
    }

    private function getConceptId($invoice): mixed
    {
        $concept = Arr::get($invoice, 'concept');

        if ($concept instanceof AfipConcept) {
            return $concept->value;
        }

        return $concept;
    }

    private function getObservations(array $response): array
    {
        $observations = Arr::get($response, 'FeDetResp.FECAEDetResponse.Observaciones.Obs');

        if (! Arr::has($observations, 0) && ! empty($observations)) {
            $observations = [$observations];
        }

        return collect($observations)
            ->map(function ($observation) {
                return [
                    'code' => $observation['Code'],
                    'message' => $observation['Msg'],
                ];
            })
            ->toArray();
    }
}
