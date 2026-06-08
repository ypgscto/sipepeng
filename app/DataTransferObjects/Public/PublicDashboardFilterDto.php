<?php

namespace App\DataTransferObjects\Public;

class PublicDashboardFilterDto
{
    public function __construct(
        public int $year,
    ) {}
}
