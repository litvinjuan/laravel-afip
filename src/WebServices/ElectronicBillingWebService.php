<?php

namespace litvinjuan\LaravelAfip\WebServices;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Arr;
use litvinjuan\LaravelAfip\AfipConfiguration;
use litvinjuan\LaravelAfip\Clients\AfipClient;
use litvinjuan\LaravelAfip\Enum\AfipConcept;
use litvinjuan\LaravelAfip\Enum\AfipInvoiceLetter;
use litvinjuan\LaravelAfip\Enum\AfipInvoiceType;
use litvinjuan\LaravelAfip\Enum\AfipService;
use litvinjuan\LaravelAfip\Exceptions\AfipAuthenticationException;
use litvinjuan\LaravelAfip\Exceptions\AfipException;
use litvinjuan\LaravelAfip\Exceptions\AfipSigningException;

class ElectronicBillingWebService
{
    private AfipConfiguration $configuration;

    private AfipClient $client;

    public function __construct(?AfipConfiguration $configuration = null)
    {
        $this->configuration = $configuration ?? new AfipConfiguration();

        $this->client = new AfipClient(AfipService::wsfe, $this->configuration);
    }

    public function getLastInvoiceNumber(AfipInvoiceType $invoiceType, int $pointOfSale): int
    {
        return $this->client->call('FECompUltimoAutorizado', [
            'Auth' => $this->getAuthData(),
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
        $invoices = $this->createInvoices($invoiceType, $pointOfSale, [$invoice]);

        // Only one invoice is created, so only the results for this invoice are returned
        $invoice = Arr::get($invoices, 0);

        if (! empty($invoice['errors'])) {
            $error = $invoice['errors'][0];
            throw new AfipException($error['message'], $error['code']);
        }

        return $invoice;
    }

    public function getInvoice(AfipInvoiceType $invoiceType, int $pointOfSale, int $invoiceNumber): ?array
    {
        try {
            return $this->client->call('FECompConsultar', [
                'Auth' => $this->getAuthData(),
                'FeCompConsReq' => [
                    'PtoVta' => $pointOfSale,
                    'CbteTipo' => $invoiceType->value,
                    'CbteNro' => $invoiceNumber,
                ],
            ]);
        } catch (AfipException $exception) {
            if ($exception->getCode() === 602) {
                // Not found in AFIP
                return null;
            }
            throw $exception;
        }
    }

    public function getPointsOfSale(): array
    {
        return $this->client->call('FEParamGetPtosVenta', [
            'Auth' => $this->getAuthData(),
        ])['ResultGet']['PtosVenta'];
    }

    public function getActivities(): array
    {
        return $this->client->call('FEParamGetActividades', [
            'Auth' => $this->getAuthData(),
        ]);
    }

    public function getMaxInvoicesPerRequest(): int
    {
        return $this->client->call('FECompTotXRequest', [
            'Auth' => $this->getAuthData(),
        ])['RegXReq'];
    }

    public function getInvoiceTypes(): array
    {
        return $this->client->call('FEParamGetTiposCbte', [
            'Auth' => $this->getAuthData(),
        ])['ResultGet']['CbteTipo'];
    }

    public function getConceptTypes(): array
    {
        return $this->client->call('FEParamGetTiposConcepto', [
            'Auth' => $this->getAuthData(),
        ])['ResultGet']['ConceptoTipo'];
    }

    public function getDocumentTypes(): array
    {
        return $this->client->call('FEParamGetTiposDoc', [
            'Auth' => $this->getAuthData(),
        ])['ResultGet']['DocTipo'];
    }

    public function getIVATypes(): array
    {
        return $this->client->call('FEParamGetTiposIva', [
            'Auth' => $this->getAuthData(),
        ])['ResultGet']['IvaTipo'];
    }

    public function getCountries(): array
    {
        return $this->client->call('FEParamGetTiposPaises', [
            'Auth' => $this->getAuthData(),
        ])['ResultGet']['PaisTipo'];
    }

    public function getCurrencies(): array
    {
        return $this->client->call('FEParamGetTiposMonedas', [
            'Auth' => $this->getAuthData(),
        ])['ResultGet']['Moneda'];
    }

    public function getOptionalTypes(): array
    {
        return $this->client->call('FEParamGetTiposOpcional', [
            'Auth' => $this->getAuthData(),
        ])['ResultGet']['OpcionalTipo'];
    }

    public function getTributeTypes(): array
    {
        return $this->client->call('FEParamGetTiposTributos', [
            'Auth' => $this->getAuthData(),
        ])['ResultGet']['TributoTipo'];
    }

    public function status(): bool
    {
        $result = $this->client->call('FEDummy');

        return collect($result['FEDummyResult'])
            ->every(function ($value, $key) {
                return $value === 'OK';
            });
    }

    /**
     * @throws AfipAuthenticationException|AfipSigningException
     */
    private function getAuthData(): array
    {
        return [
            'Token' => $this->client->getToken(),
            'Sign' => $this->client->getSign(),
            'Cuit' => $this->configuration->getCuit(),
        ];
    }

    public function createInvoices(AfipInvoiceType $invoiceType, int $pointOfSale, array $invoices): array
    {
        $invoices = Arr::isList($invoices) ? $invoices : [$invoices];

        $lastInvoiceNumber = $this->getLastInvoiceNumber($invoiceType, $pointOfSale);

        $data = [
            'FeCAEReq' => [
                'FeCabReq' => [
                    'CantReg' => count($invoices),
                    'PtoVta' => $pointOfSale,
                    'CbteTipo' => $invoiceType->value,
                ],
                'FeDetReq' => [
                    'FECAEDetRequest' => collect($invoices)->map(function ($invoice, $index) use ($lastInvoiceNumber, $invoiceType) {
                        $number = $lastInvoiceNumber + $index + 1;

                        if ($invoiceType->letter() === AfipInvoiceLetter::C && Arr::has($invoice, 'subtotal')) {
                            $invoice['net_taxed_total'] = Arr::get($invoice, 'subtotal');
                        }

                        return collect([
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
                        ])
                            ->reject(function ($value) {
                                return is_null($value);
                            })
                            ->reject(function ($value) {
                                return is_array($value) && empty($value);
                            })
                            ->toArray();
                    })->toArray(),
                ],
            ],
        ];

        $response = $this->client->call('FECAESolicitar', [
            'Auth' => $this->getAuthData(),
            ...$data,
        ]);

        $createdInvoices = Arr::get($response, 'FeDetResp.FECAEDetResponse');

        if (! array_is_list($createdInvoices)) {
            $createdInvoices = [$createdInvoices];
        }

        return array_map(function ($invoice) {
            $created = Arr::get($invoice, 'Resultado', 'R') !== 'R';
            $errors = $this->getObservations($invoice);

            return [
                'created' => $created,
                'cae' => $created ? Arr::get($invoice, 'CAE') : null,
                'cae_expiration_date' => $created ? $this->parseDate(Arr::get($invoice, 'CAEFchVto')) : null,
                'invoice_number' => $created ? Arr::get($invoice, 'CbteDesde') : null,
                'errors' => $errors,
            ];
        }, $createdInvoices);
    }

    private function formatDate(string|DateTimeInterface|Carbon|null $date): ?string
    {
        if (! $date) {
            return null;
        }

        if (! $date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        return $date->format('Ymd');
    }

    private function parseDate(string|DateTimeInterface|null $date): ?Carbon
    {
        if (! $date) {
            return null;
        }

        return Carbon::parse($date);
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

    private function getObservations(array $invoice): array
    {
        $observations = Arr::get($invoice, 'Observaciones.Obs');

        if (! $observations) {
            return [];
        }

        if (! array_is_list($observations)) {
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
