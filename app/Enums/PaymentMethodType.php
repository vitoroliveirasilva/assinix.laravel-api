<?php

namespace App\Enums;

enum PaymentMethodType: string
{
    case CreditCard = 'credit_card';
    case DebitCard = 'debit_card';
    case Pix = 'pix';
    case BankSlip = 'bank_slip';
    case BankTransfer = 'bank_transfer';
    case Cash = 'cash';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CreditCard => 'Cartão de crédito',
            self::DebitCard => 'Cartão de débito',
            self::Pix => 'Pix',
            self::BankSlip => 'Boleto',
            self::BankTransfer => 'Transferência bancária',
            self::Cash => 'Dinheiro',
            self::Other => 'Outro',
        };
    }

    public static function values(): array
    {
        return array_map(
            callback: static fn (self $type): string => $type->value,
            array: self::cases(),
        );
    }
}
