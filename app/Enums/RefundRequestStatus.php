<?php

namespace App\Enums;

enum RefundRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Processed = 'processed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Approved => 'Aprovado',
            self::Rejected => 'Rejeitado',
            self::Processed => 'Processado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Processed => 'primary',
        };
    }
}
