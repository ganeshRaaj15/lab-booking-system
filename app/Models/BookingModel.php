<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table            = 'bookings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'user_id',
        'lab_id',
        'user_type',
        'faculty_id',
        'approval_flow',
        'approved_by_pic',
        'approved_by_manager',
        'date',
        'start_time',
        'end_time',
        'activity',
        'supervisor_name',
        'supervisor_email',
        'supervisor_phone',
        'pdf_path',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * ---------------------------------------------------------
     * BASIC HELPERS
     * ---------------------------------------------------------
     */

    public function getBookingsForDate($labId, $date)
    {
        return $this->where('lab_id', $labId)
                    ->where('date', $date)
                    ->findAll();
    }

    public function slotTaken($labId, $date, $start, $end)
    {
        return $this->where('lab_id', $labId)
                    ->where('date', $date)
                    ->groupStart()
                        ->where("start_time <", $end)
                        ->where("end_time >", $start)
                    ->groupEnd()
                    ->countAllResults() > 0;
    }

    /**
     * =========================================================
     * 🔹 ANALYTICS HELPERS (FOR DASHBOARDS)
     * =========================================================
     */

    public function countByStatus(): array
    {
        $db = \Config\Database::connect();

        return [
            'pending' => $db->table('bookings')
                ->where('status', 'PENDING')
                ->where('approved_by_pic', 0)
                ->countAllResults(),

            'pending_mgr' => $db->table('bookings')
                ->where('status', 'PENDING')
                ->where('approved_by_pic', 1)
                ->where('approved_by_manager', 0)
                ->countAllResults(),

            'approved' => $db->table('bookings')
                ->where('status', 'APPROVED')
                ->countAllResults(),

            'rejected' => $db->table('bookings')
                ->where('status', 'REJECTED')
                ->countAllResults(),
        ];
    }

    public function picApprovalCount(): int
    {
        return $this->where('approved_by_pic', 1)->countAllResults();
    }

    public function managerApprovalCount(): int
    {
        return $this->where('approved_by_manager', 1)->countAllResults();
    }

    /**
     * Trend by month
     */
    public function monthlyTrend(): array
    {
        return $this->select("DATE_FORMAT(date, '%Y-%m') AS month, COUNT(*) AS total")
                    ->whereIn('status', ['PENDING', 'APPROVED', 'REJECTED'])
                    ->groupBy("DATE_FORMAT(date, '%Y-%m')")
                    ->orderBy("month", "ASC")
                    ->findAll();
    }

    /**
     * Returns bookings grouped per month for past X months
     */
    public function getMonthlyBookings(int $months = 6)
    {
        $sql = "
            SELECT DATE_FORMAT(date, '%Y-%m') AS month, COUNT(*) AS count
            FROM bookings
            WHERE date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
              AND status IN ('PENDING', 'APPROVED', 'REJECTED')
            GROUP BY DATE_FORMAT(date, '%Y-%m')
            ORDER BY month ASC
        ";

        return $this->db->query($sql, [$months])->getResultArray();
    }

    /**
     * Count bookings grouped by faculty (JOIN faculties table)
     */
    public function getBookingsPerFaculty()
    {
        $sql = "
            SELECT 
                faculties.name_en AS faculty_name,
                COUNT(*) AS count
            FROM bookings
            LEFT JOIN faculties ON faculties.id = bookings.faculty_id
            GROUP BY faculties.name_en
            ORDER BY count DESC
        ";

        return $this->db->query($sql)->getResultArray();
    }

    /**
     * Count bookings grouped per laboratory (JOIN labs table)
     */
    public function getBookingsPerLab()
    {
        $sql = "
            SELECT 
                laboratories.name AS lab_name,
                COUNT(*) AS count
            FROM bookings
            LEFT JOIN laboratories ON laboratories.id = bookings.lab_id
            GROUP BY laboratories.name
            ORDER BY count DESC
        ";

        return $this->db->query($sql)->getResultArray();
    }
}

