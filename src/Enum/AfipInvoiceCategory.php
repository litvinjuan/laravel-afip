<?php

namespace litvinjuan\LaravelAfip\Enum;

enum AfipInvoiceCategory
{
    case Factura;
    case NotaDeDebito;
    case NotaDeCredito;
    case Recibo;
    case Other;
}
