<?php

namespace App\Utils\Traits;

use DateTime;

trait DateFilterTrait
{
    private function applyDateFilters($query, $periodFrom, $periodTo)
    {
        if ($periodFrom && $periodTo) {
            $dateTimeFrom = DateTime::createFromFormat('Y-m-d', $periodFrom);
            $dateTimeTo = DateTime::createFromFormat('Y-m-d', $periodTo);

            if (!$dateTimeFrom || !$dateTimeTo) {
                $dateTimeFrom = null;
                $dateTimeTo = null;
            }

            $query->whereBetween('created_at', [$dateTimeFrom, $dateTimeTo]);
        }
    }
}
