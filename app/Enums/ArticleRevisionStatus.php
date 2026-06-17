<?php

namespace App\Enums;

enum ArticleRevisionStatus: string
{
    case Review = 'review';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Review => 'Pembaruan Menunggu Review',
            self::Approved => 'Pembaruan Disetujui',
            self::Rejected => 'Pembaruan Perlu Perbaikan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Review => 'border-violet-200 bg-violet-50 text-violet-700',
            self::Approved => 'border-lime-200 bg-lime-50 text-lime-700',
            self::Rejected => 'border-rose-200 bg-rose-50 text-rose-700',
        };
    }

    public function dotClass(): string
    {
        return match ($this) {
            self::Review => 'bg-violet-500',
            self::Approved => 'bg-lime-500',
            self::Rejected => 'bg-rose-500',
        };
    }
}
