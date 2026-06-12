<?php

namespace App\Libraries;

use App\Services\ReportAnalyticsService;
use CodeIgniter\Shield\Entities\User;

class ReportSnapshotBuilder
{
    protected ReportAnalyticsService $reports;

    public function __construct()
    {
        $this->reports = new ReportAnalyticsService();
    }

    public function build(User $user, array $filters = []): array
    {
        return $this->reports->build($user, $filters);
    }

    public function buildCsv(array $data): string
    {
        return $this->reports->buildCsv($data);
    }
}
