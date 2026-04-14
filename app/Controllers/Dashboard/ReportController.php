<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use Dompdf\Dompdf;

class ReportController extends BaseController
{
    public function download()
    {
        helper('auth');

        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();
        $role = 'user';
        if ($user->inGroup('admin')) {
            $role = 'admin';
        } elseif ($user->inGroup('manager')) {
            $role = 'manager';
        } elseif ($user->inGroup('pic')) {
            $role = 'pic';
        }

        if (!in_array($role, ['admin', 'manager', 'pic'], true)) {
            return redirect()->back()->with('error', 'You do not have access to reports.');
        }

        $db = \Config\Database::connect();
        $email = $db->table('auth_identities')
            ->where('user_id', $user->id)
            ->where('type', 'email_password')
            ->get()
            ->getRow('secret') ?? '';

        $labIds = [];
        if ($role === 'pic') {
            $labIds = $db->table('laboratories')
                ->select('id')
                ->where('pic_email', $email)
                ->get()
                ->getResultArray();
            $labIds = array_map(static fn($row) => (int) $row['id'], $labIds);
        }

        $applyLabScope = function ($builder, string $column = 'lab_id') use ($labIds, $role) {
            if ($role === 'pic') {
                if (empty($labIds)) {
                    $builder->where('1 = 0');
                } else {
                    $builder->whereIn($column, $labIds);
                }
            }
            return $builder;
        };

        $labsQuery = $db->table('laboratories');
        if ($role === 'pic') {
            if (empty($labIds)) {
                $labsQuery->where('1 = 0');
            } else {
                $labsQuery->whereIn('id', $labIds);
            }
        }
        $labs = $labsQuery->get()->getResultArray();

        $assetsStatus = $applyLabScope($db->table('assets')->select('status, COUNT(*) AS total')->groupBy('status'))
            ->get()
            ->getResultArray();

        $statusFilter = ['PENDING', 'APPROVED', 'REJECTED'];
        $statusCounts = $applyLabScope(
            $db->table('bookings')
                ->select('status, COUNT(*) AS total')
                ->whereIn('status', $statusFilter)
                ->groupBy('status')
        )->get()->getResultArray();

        $statusMap = ['APPROVED' => 0, 'PENDING' => 0, 'REJECTED' => 0];
        foreach ($statusCounts as $row) {
            $statusMap[$row['status']] = (int) $row['total'];
        }

        $totalBookings = array_sum($statusMap);
        $totalLabs = count($labs);

        $assetTotals = ['available' => 0, 'maintenance' => 0, 'faulty' => 0];
        foreach ($assetsStatus as $row) {
            $assetTotals[$row['status']] = (int) $row['total'];
        }
        $assetsTotal = array_sum($assetTotals);

        $monthlyCutoff = date('Y-m-d', strtotime('-6 months'));
        $monthlyTrend = $applyLabScope(
            $db->table('bookings')
                ->select("DATE_FORMAT(date, '%Y-%m') AS month, COUNT(*) AS total")
                ->where('date >=', $monthlyCutoff)
                ->whereIn('status', $statusFilter)
                ->groupBy("DATE_FORMAT(date, '%Y-%m')")
                ->orderBy('month', 'ASC')
        )->get()->getResultArray();

        $topLabsBuilder = $db->table('bookings b')
            ->select('l.name AS lab_name, COUNT(*) AS total')
            ->join('laboratories l', 'l.id = b.lab_id', 'left')
            ->groupBy('l.name')
            ->orderBy('total', 'DESC')
            ->limit(5);
        if (in_array($role, ['pic', 'manager'], true)) {
            $topLabsBuilder->where('b.status', 'APPROVED');
        }
        if ($role === 'pic') {
            if (empty($labIds)) {
                $topLabsBuilder->where('1 = 0');
            } else {
                $topLabsBuilder->whereIn('b.lab_id', $labIds);
            }
        }
        $topLabs = $topLabsBuilder->get()->getResultArray();

        $facultyBuilder = $db->table('bookings b')
            ->select('f.name_en AS faculty_name, COUNT(*) AS total')
            ->join('faculties f', 'f.id = b.faculty_id', 'left')
            ->groupBy('f.name_en')
            ->orderBy('total', 'DESC')
            ->limit(6);
        if (in_array($role, ['pic', 'manager'], true)) {
            $facultyBuilder->where('b.status', 'APPROVED');
        }
        if ($role === 'pic') {
            if (empty($labIds)) {
                $facultyBuilder->where('1 = 0');
            } else {
                $facultyBuilder->whereIn('b.lab_id', $labIds);
            }
        }
        $facultyCounts = $facultyBuilder->get()->getResultArray();

        $maintenanceQuery = $db->table('maintenance_records mr')
            ->select('mr.status, COUNT(*) AS total')
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->groupBy('mr.status');
        $maintenanceQuery = $applyLabScope($maintenanceQuery, 'a.lab_id');
        $maintenanceRows = $maintenanceQuery->get()->getResultArray();
        $maintenanceStatus = ['reported' => 0, 'scheduled' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($maintenanceRows as $row) {
            $maintenanceStatus[$row['status']] = (int) $row['total'];
        }

        $maintenanceTrendQuery = $db->table('maintenance_records mr')
            ->select("DATE_FORMAT(mr.created_at, '%Y-%m') AS month, COUNT(*) AS total")
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->where('mr.created_at >=', date('Y-m-d', strtotime('-6 months')))
            ->groupBy("DATE_FORMAT(mr.created_at, '%Y-%m')")
            ->orderBy('month', 'ASC');
        $maintenanceTrendQuery = $applyLabScope($maintenanceTrendQuery, 'a.lab_id');
        $maintenanceTrend = $maintenanceTrendQuery->get()->getResultArray();

        $topMaintenanceAssetsQuery = $db->table('maintenance_records mr')
            ->select('a.name AS asset_name, COUNT(*) AS total')
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->groupBy('a.name')
            ->orderBy('total', 'DESC')
            ->limit(5);
        $topMaintenanceAssetsQuery = $applyLabScope($topMaintenanceAssetsQuery, 'a.lab_id');
        $topMaintenanceAssets = $topMaintenanceAssetsQuery->get()->getResultArray();

        $upcomingApprovalsQuery = $db->table('bookings b')
            ->select('l.name AS lab_name, b.date, b.start_time, b.end_time, b.status, b.approval_flow')
            ->join('laboratories l', 'l.id = b.lab_id', 'left')
            ->where('b.date >=', date('Y-m-d'))
            ->whereIn('b.status', ['PENDING', 'APPROVED'])
            ->orderBy('b.date', 'ASC')
            ->orderBy('b.start_time', 'ASC')
            ->limit(8);
        $upcomingApprovalsQuery = $applyLabScope($upcomingApprovalsQuery, 'b.lab_id');
        $upcomingBookings = $upcomingApprovalsQuery->get()->getResultArray();

        $userCount = $role === 'admin' ? $db->table('users')->countAllResults() : null;

        $data = [
            'reportTitle' => strtoupper($role) . ' Analytics Report',
            'scopeLabel' => $role === 'pic' ? 'PIC Scope (Assigned Labs)' : 'System-wide Scope',
            'generatedAt' => date('Y-m-d H:i'),
            'kpis' => [
                'total_bookings' => $totalBookings,
                'approved' => $statusMap['APPROVED'],
                'pending' => $statusMap['PENDING'],
                'rejected' => $statusMap['REJECTED'],
                'total_labs' => $totalLabs,
                'total_assets' => $assetsTotal,
                'users' => $userCount,
                'maintenance_total' => array_sum($maintenanceStatus),
                'maintenance_open' => $maintenanceStatus['reported'] + $maintenanceStatus['scheduled'],
                'maintenance_completed' => $maintenanceStatus['completed'],
            ],
            'assetTotals' => $assetTotals,
            'statusMap' => $statusMap,
            'monthlyTrend' => $monthlyTrend,
            'topLabs' => $topLabs,
            'facultyCounts' => $facultyCounts,
            'labs' => $labs,
            'maintenanceStatus' => $maintenanceStatus,
            'maintenanceTrend' => $maintenanceTrend,
            'topMaintenanceAssets' => $topMaintenanceAssets,
            'upcomingBookings' => $upcomingBookings,
            'role' => $role,
        ];

        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $html = view('reports/summary_pdf', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'slams-report-' . $role . '-' . date('Ymd_His') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }
}
