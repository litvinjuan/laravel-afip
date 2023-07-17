<?php

namespace litvinjuan\LaravelAfip\Enum;

enum AfipInvoiceType: int
{
    case FacturaA = 1;
    case NotaDeDebitoA = 2;
    case NotaDeCreditoA = 3;
    case ReciboA = 4;
    case NotaVentaContadoA = 5;
    case LiquidacionA = 63;
    case FacturaB = 6;
    case NotaDeDebitoB = 7;
    case NotaDeCreditoB = 8;
    case ReciboB = 9;
    case NotaVentaContadoB = 10;
    case LiquidacionB = 64;
    case FacturaC = 11;
    case NotaDeDebitoC = 12;
    case NotaDeCreditoC = 13;
    case ReciboC = 15;
    case FacturaM = 51;
    case NotaDeDebitoM = 52;
    case NotaDeCreditoM = 53;
    case ReciboM = 54;
    case CompraBienesUsadosAConsumidorFinal = 49;

    case ComprobanteAResolucion1415 = 34;
    case ComprobanteBResolucion1415 = 35;
    case OtrosComprobantesACumplenResolucion1415 = 39;
    case OtrosComprobantesBCumplenResolucion1415 = 40;
    case CuentaDeVentaYLiquidoProductoA = 60;
    case CuentaDeVentaYLiquidoProductoB = 61;

    case FacturaElectronicaMiPYMEsA = 201;
    case NotaDeDebitoElectronicaMiPYMEsA = 202;
    case NotaDeCreditoElectronicaMiPYMEsA = 203;
    case FacturaElectronicaMiPYMEsB = 206;
    case NotaDeDebitoElectronicaMiPYMEsB = 207;
    case NotaDeCreditoElectronicaMiPYMEsB = 208;
    case FacturaCElectronicaMiPYMEs = 211;
    case NotaDeDebitoCElectronicaMiPYMEs = 212;
    case NotaDeCreditoCElectronicaMiPYMEs = 213;

    public function description(): string
    {
        return match ($this) {
            self::FacturaA => 'Factura A',
            self::NotaDeDebitoA => 'Nota de Débito A',
            self::NotaDeCreditoA => 'Nota de Crédito A',
            self::ReciboA => 'Recibos A',
            self::NotaVentaContadoA => 'Notas de Venta al contado A',
            self::LiquidacionA => 'Liquidacion A',
            self::FacturaB => 'Factura B',
            self::NotaDeDebitoB => 'Nota de Débito B',
            self::NotaDeCreditoB => 'Nota de Crédito B',
            self::ReciboB => 'Recibos B',
            self::NotaVentaContadoB => 'Notas de Venta al contado B',
            self::LiquidacionB => 'Liquidacion B',
            self::FacturaC => 'Factura C',
            self::NotaDeDebitoC => 'Nota de Débito C',
            self::NotaDeCreditoC => 'Nota de Crédito C',
            self::ReciboC => 'Recibo C',
            self::FacturaM => 'Factura M',
            self::NotaDeDebitoM => 'Nota de Débito M',
            self::NotaDeCreditoM => 'Nota de Crédito M',
            self::ReciboM => 'Recibo M',
            self::CompraBienesUsadosAConsumidorFinal => 'Comprobante de Compra de Bienes Usados a Consumidor Final',
            self::ComprobanteAResolucion1415 => 'Cbtes. A del Anexo I, Apartado A,inc.f),R.G.Nro. 1415',
            self::ComprobanteBResolucion1415 => 'Cbtes. B del Anexo I,Apartado A,inc. f),R.G. Nro. 1415',
            self::OtrosComprobantesACumplenResolucion1415 => 'Otros comprobantes A que cumplan con R.G.Nro. 1415',
            self::OtrosComprobantesBCumplenResolucion1415 => 'Otros comprobantes B que cumplan con R.G.Nro. 1415',
            self::CuentaDeVentaYLiquidoProductoA => 'Cta de Vta y Liquido prod. A',
            self::CuentaDeVentaYLiquidoProductoB => 'Cta de Vta y Liquido prod. B',
            self::FacturaElectronicaMiPYMEsA => 'Factura de Crédito electrónica MiPyMEs (FCE) A',
            self::NotaDeDebitoElectronicaMiPYMEsA => 'Nota de Débito electrónica MiPyMEs (FCE) A',
            self::NotaDeCreditoElectronicaMiPYMEsA => 'Nota de Crédito electrónica MiPyMEs (FCE) A',
            self::FacturaElectronicaMiPYMEsB => 'Factura de Crédito electrónica MiPyMEs (FCE) B',
            self::NotaDeDebitoElectronicaMiPYMEsB => 'Nota de Débito electrónica MiPyMEs (FCE) B',
            self::NotaDeCreditoElectronicaMiPYMEsB => 'Nota de Crédito electrónica MiPyMEs (FCE) B',
            self::FacturaCElectronicaMiPYMEs => 'Factura de Crédito electrónica MiPyMEs (FCE) C',
            self::NotaDeDebitoCElectronicaMiPYMEs => 'Nota de Débito electrónica MiPyMEs (FCE) C',
            self::NotaDeCreditoCElectronicaMiPYMEs => 'Nota de Crédito electrónica MiPyMEs (FCE) C',
        };
    }

    public function letter(): AfipInvoiceLetter
    {
        return match ($this) {
            self::FacturaA, self::NotaDeDebitoA, self::NotaDeCreditoA, self::ReciboA, self::NotaDeCreditoElectronicaMiPYMEsA, self::NotaDeDebitoElectronicaMiPYMEsA, self::FacturaElectronicaMiPYMEsA, self::CuentaDeVentaYLiquidoProductoA, self::OtrosComprobantesACumplenResolucion1415, self::ComprobanteAResolucion1415, self::LiquidacionA, self::NotaVentaContadoA => AfipInvoiceLetter::A,
            self::FacturaB, self::NotaDeCreditoElectronicaMiPYMEsB, self::NotaDeDebitoElectronicaMiPYMEsB, self::FacturaElectronicaMiPYMEsB, self::CuentaDeVentaYLiquidoProductoB, self::OtrosComprobantesBCumplenResolucion1415, self::ComprobanteBResolucion1415, self::LiquidacionB, self::NotaVentaContadoB, self::ReciboB, self::NotaDeCreditoB, self::NotaDeDebitoB => AfipInvoiceLetter::B,
            self::FacturaC, self::FacturaCElectronicaMiPYMEs, self::NotaDeCreditoCElectronicaMiPYMEs, self::NotaDeDebitoCElectronicaMiPYMEs, self::ReciboC, self::NotaDeCreditoC, self::NotaDeDebitoC => AfipInvoiceLetter::C,
            self::FacturaM, self::ReciboM, self::NotaDeCreditoM, self::NotaDeDebitoM => AfipInvoiceLetter::M,
            self::CompraBienesUsadosAConsumidorFinal => AfipInvoiceLetter::None,
        };
    }

    public function category(): AfipInvoiceCategory
    {
        return match ($this) {
            self::FacturaA, self::FacturaM, self::FacturaC, self::FacturaB => AfipInvoiceCategory::Factura,
            self::NotaDeDebitoA, self::NotaDeDebitoM, self::NotaDeDebitoC, self::NotaDeDebitoB => AfipInvoiceCategory::NotaDeDebito,
            self::NotaDeCreditoA, self::NotaDeCreditoM, self::NotaDeCreditoC, self::NotaDeCreditoB => AfipInvoiceCategory::NotaDeCredito,
            self::ReciboA, self::ReciboM, self::ReciboC, self::ReciboB => AfipInvoiceCategory::Recibo,
            self::NotaVentaContadoA, self::NotaDeCreditoCElectronicaMiPYMEs, self::NotaDeDebitoCElectronicaMiPYMEs, self::FacturaCElectronicaMiPYMEs, self::NotaDeCreditoElectronicaMiPYMEsB, self::NotaDeDebitoElectronicaMiPYMEsB, self::FacturaElectronicaMiPYMEsB, self::NotaDeCreditoElectronicaMiPYMEsA, self::NotaDeDebitoElectronicaMiPYMEsA, self::FacturaElectronicaMiPYMEsA, self::CuentaDeVentaYLiquidoProductoB, self::CuentaDeVentaYLiquidoProductoA, self::OtrosComprobantesBCumplenResolucion1415, self::OtrosComprobantesACumplenResolucion1415, self::ComprobanteBResolucion1415, self::ComprobanteAResolucion1415, self::CompraBienesUsadosAConsumidorFinal, self::LiquidacionB, self::NotaVentaContadoB, self::LiquidacionA => AfipInvoiceCategory::Other,
        };
    }
}
