<?php

namespace App\Support;

use App\Models\RelatedTo;
use App\Models\Status;
use Illuminate\Support\Collection;

class ObeStatus
{
    /**
     * Status rows intended for OBE modules (Related To = "OBE" when present; otherwise all statuses).
     */
    public static function forDropdown(): Collection
    {
        $relatedId = RelatedTo::query()->where('name', 'OBE')->value('id');

        return Status::query()
            ->when($relatedId, fn ($q) => $q->where('related_to_id', $relatedId))
            ->orderBy('status_name')
            ->get();
    }

    public static function isActiveStatusId(int $statusId): bool
    {
        return Status::query()
            ->whereKey($statusId)
            ->whereRaw('LOWER(status_name) = ?', ['active'])
            ->exists();
    }
}
