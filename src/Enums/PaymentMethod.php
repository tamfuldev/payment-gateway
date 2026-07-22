<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Enums;

/**
 * Normalized payment method (used to hint the UI or pick the right flow).
 */
enum PaymentMethod: string
{
    case Redirect = 'redirect';   // Redirect to the gateway's page (VNPay, OnePay...)
    case QrCode = 'qr_code';      // Display a QR code (VietQR, MoMo QR...)
    case Card = 'card';           // Direct card entry (Stripe...)
    case Wallet = 'wallet';       // E-wallet (MoMo, ZaloPay, Binance Pay...)
    case BankTransfer = 'bank_transfer';
}
