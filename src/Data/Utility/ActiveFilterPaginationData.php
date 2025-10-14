<?php

namespace TautId\Payment\Data\Utility;

use Spatie\LaravelData\Data;

class ActiveFilterPaginationData extends Data
{
    public function __construct(
        public string $column,
        public string $value
    )
    {

    }
}
