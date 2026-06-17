<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Review => 'Menunggu Review',
            self::Published => 'Terbit',
            self::Rejected => 'Perlu Perbaikan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'border-amber-200 bg-amber-50 text-amber-700',
            self::Review => 'border-sky-200 bg-sky-50 text-sky-700',
            self::Published => 'border-lime-200 bg-lime-50 text-lime-700',
            self::Rejected => 'border-rose-200 bg-rose-50 text-rose-700',
        };
    }

    public function dotClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-amber-500',
            self::Review => 'bg-sky-500',
            self::Published => 'bg-lime-500',
            self::Rejected => 'bg-rose-500',
        };
    }
}
