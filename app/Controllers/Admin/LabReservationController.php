<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LabReservationModel;
use App\Models\LaboratoryModel;

class LabReservationController extends BaseController
{
    protected LabReservationModel $model;
    protected LaboratoryModel $labModel;

    protected array $dayNames = [
        0 => 'Monday', 1 => 'Tuesday', 2 => 'Wednesday',
        3 => 'Thursday', 4 => 'Friday', 5 => 'Saturday', 6 => 'Sunday',
    ];

    public function __construct()
    {
        helper('auth');
        if (! auth()->loggedIn() || ! auth()->user()->inGroup('admin')) {
            redirect()->to('/dashboard')->with('error', 'Access denied.')->send();
            exit;
        }
        $this->model    = new LabReservationModel();
        $this->labModel = new LaboratoryModel();
    }

    public function index()
    {
        $filters = [
            'lab_id' => (int) $this->request->getGet('lab_id'),
            'type'   => trim((string) $this->request->getGet('type')),
        ];
        if (! in_array($filters['type'], ['manual', 'class'], true)) {
            $filters['type'] = '';
        }

        $reservations = $this->model->getAll($filters);
        $labs         = $this->labModel->orderBy('name', 'ASC')->findAll();

        return view('admin/reservations/index', [
            'title'        => 'Lab Reservations',
            'reservations' => $reservations,
            'labs'         => $labs,
            'filters'      => $filters,
            'dayNames'     => $this->dayNames,
        ]);
    }

    public function create()
    {
        return view('admin/reservations/form', [
            'title'    => 'Add Reservation',
            'labs'     => $this->labModel->orderBy('name', 'ASC')->findAll(),
            'dayNames' => $this->dayNames,
            'record'   => null,
        ]);
    }

    public function store()
    {
        $data = $this->buildPayload();
        if (is_string($data)) {
            return redirect()->back()->withInput()->with('error', $data);
        }

        $this->model->insert($data);

        return redirect()->to('/admin/reservations')->with('message', 'Reservation added successfully.');
    }

    public function edit(int $id)
    {
        $record = $this->model->find($id);
        if (! $record) {
            return redirect()->to('/admin/reservations')->with('error', 'Reservation not found.');
        }

        return view('admin/reservations/form', [
            'title'    => 'Edit Reservation',
            'labs'     => $this->labModel->orderBy('name', 'ASC')->findAll(),
            'dayNames' => $this->dayNames,
            'record'   => $record,
        ]);
    }

    public function update(int $id)
    {
        if (! $this->model->find($id)) {
            return redirect()->to('/admin/reservations')->with('error', 'Reservation not found.');
        }

        $data = $this->buildPayload();
        if (is_string($data)) {
            return redirect()->back()->withInput()->with('error', $data);
        }

        $this->model->update($id, $data);

        return redirect()->to('/admin/reservations')->with('message', 'Reservation updated successfully.');
    }

    public function delete(int $id)
    {
        if (! $this->model->find($id)) {
            return redirect()->to('/admin/reservations')->with('error', 'Reservation not found.');
        }

        $this->model->delete($id);

        return redirect()->to('/admin/reservations')->with('message', 'Reservation deleted.');
    }

    // -------------------------------------------------------------------------
    // CSV Upload
    // -------------------------------------------------------------------------

    public function uploadForm()
    {
        return view('admin/reservations/upload', [
            'title' => 'Upload Class Schedule',
            'labs'  => $this->labModel->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function uploadPreview()
    {
        $file = $this->request->getFile('csv');
        if (! $file || ! $file->isValid() || $file->getMimeType() !== 'text/csv' && strtolower($file->getClientExtension()) !== 'csv') {
            return redirect()->back()->with('error', 'Please upload a valid CSV file.');
        }

        $labs = [];
        foreach ($this->labModel->findAll() as $lab) {
            $labs[strtolower(trim((string) $lab['name']))] = (int) $lab['id'];
        }

        $dayMap = [
            'monday' => 0, 'mon' => 0,
            'tuesday' => 1, 'tue' => 1,
            'wednesday' => 2, 'wed' => 2,
            'thursday' => 3, 'thu' => 3,
            'friday' => 4, 'fri' => 4,
            'saturday' => 5, 'sat' => 5,
            'sunday' => 6, 'sun' => 6,
        ];

        $rows   = [];
        $handle = fopen($file->getTempName(), 'r');
        $header = fgetcsv($handle); // skip header row

        $lineNum = 1;
        while (($cols = fgetcsv($handle)) !== false) {
            $lineNum++;
            if (count($cols) < 6) {
                $rows[] = ['line' => $lineNum, 'error' => 'Not enough columns (expected at least 6).', 'raw' => implode(',', $cols)];
                continue;
            }

            [$labName, $dayRaw, $startTime, $endTime, $subjectCode, $subjectName] = array_map('trim', $cols);
            $validFrom  = isset($cols[6]) ? trim($cols[6]) : null;
            $validUntil = isset($cols[7]) ? trim($cols[7]) : null;

            $labId = $labs[strtolower($labName)] ?? null;
            if (! $labId) {
                $rows[] = ['line' => $lineNum, 'error' => 'Lab not found: "' . $labName . '"', 'raw' => implode(',', $cols)];
                continue;
            }

            $dow = $dayMap[strtolower($dayRaw)] ?? null;
            if ($dow === null) {
                $rows[] = ['line' => $lineNum, 'error' => 'Unrecognised day: "' . $dayRaw . '"', 'raw' => implode(',', $cols)];
                continue;
            }

            $startTime = $this->normalizeTime($startTime);
            $endTime   = $this->normalizeTime($endTime);
            if (! $startTime || ! $endTime || $startTime >= $endTime) {
                $rows[] = ['line' => $lineNum, 'error' => 'Invalid time range.', 'raw' => implode(',', $cols)];
                continue;
            }

            if ($validFrom !== '' && $validFrom !== null && ! $this->isValidDate($validFrom)) {
                $rows[] = ['line' => $lineNum, 'error' => 'Invalid valid_from date.', 'raw' => implode(',', $cols)];
                continue;
            }
            if ($validUntil !== '' && $validUntil !== null && ! $this->isValidDate($validUntil)) {
                $rows[] = ['line' => $lineNum, 'error' => 'Invalid valid_until date.', 'raw' => implode(',', $cols)];
                continue;
            }
            if ($validFrom && $validUntil && $validFrom >= $validUntil) {
                $rows[] = ['line' => $lineNum, 'error' => 'valid_from must be before valid_until.', 'raw' => implode(',', $cols)];
                continue;
            }

            $title = trim($subjectCode . ($subjectName !== '' ? ' – ' . $subjectName : ''));

            $rows[] = [
                'line'        => $lineNum,
                'error'       => null,
                'lab_id'      => $labId,
                'lab_name'    => $labName,
                'type'        => 'class',
                'title'       => $title,
                'recurrence'  => 'weekly',
                'day_of_week' => $dow,
                'day_label'   => $this->dayNames[$dow],
                'start_time'  => $startTime,
                'end_time'    => $endTime,
                'valid_from'  => ($validFrom ?: null),
                'valid_until' => ($validUntil ?: null),
            ];
        }
        fclose($handle);

        session()->set('csv_preview', $rows);

        return view('admin/reservations/upload_preview', [
            'title'    => 'Preview Class Schedule Import',
            'rows'     => $rows,
            'dayNames' => $this->dayNames,
        ]);
    }

    public function uploadConfirm()
    {
        $rows = session()->get('csv_preview') ?? [];
        session()->remove('csv_preview');

        $userId = auth()->id();
        $now    = date('Y-m-d H:i:s');
        $count  = 0;

        foreach ($rows as $row) {
            if (! empty($row['error'])) {
                continue;
            }
            $this->model->insert([
                'lab_id'      => $row['lab_id'],
                'type'        => 'class',
                'title'       => $row['title'],
                'recurrence'  => 'weekly',
                'day_of_week' => $row['day_of_week'],
                'start_time'  => $row['start_time'],
                'end_time'    => $row['end_time'],
                'valid_from'  => $row['valid_from'],
                'valid_until' => $row['valid_until'],
                'created_by'  => $userId,
                'created_at'  => $now,
            ]);
            $count++;
        }

        return redirect()->to('/admin/reservations')
            ->with('message', $count . ' class schedule ' . ($count === 1 ? 'entry' : 'entries') . ' imported successfully.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function buildPayload(): array|string
    {
        $recurrence = $this->request->getPost('recurrence');
        if (! in_array($recurrence, ['none', 'weekly'], true)) {
            return 'Invalid recurrence type.';
        }

        $labId = (int) $this->request->getPost('lab_id');
        if ($labId <= 0 || ! $this->labModel->find($labId)) {
            return 'Please select a valid laboratory.';
        }

        $type = $this->request->getPost('type');
        if (! in_array($type, ['manual', 'class'], true)) {
            return 'Invalid reservation type.';
        }

        $title = trim((string) $this->request->getPost('title'));
        if ($title === '') {
            return 'Please provide a title.';
        }

        $start = $this->normalizeTime((string) $this->request->getPost('start_time'));
        $end   = $this->normalizeTime((string) $this->request->getPost('end_time'));
        if (! $start || ! $end || $start >= $end) {
            return 'Invalid time range.';
        }

        $date      = null;
        $dow       = null;
        $validFrom = null;
        $validUntil = null;

        if ($recurrence === 'none') {
            $date = trim((string) $this->request->getPost('date'));
            if (! $this->isValidDate($date)) {
                return 'Please provide a valid date.';
            }
        } else {
            $dow = (int) $this->request->getPost('day_of_week');
            if ($dow < 0 || $dow > 6) {
                return 'Please select a valid day of the week.';
            }
            $validFrom  = trim((string) $this->request->getPost('valid_from'))  ?: null;
            $validUntil = trim((string) $this->request->getPost('valid_until')) ?: null;
            if ($validFrom && $validUntil && $validFrom >= $validUntil) {
                return 'Valid from must be before valid until.';
            }
        }

        return [
            'lab_id'      => $labId,
            'type'        => $type,
            'title'       => $title,
            'recurrence'  => $recurrence,
            'date'        => $date,
            'day_of_week' => $dow,
            'start_time'  => $start,
            'end_time'    => $end,
            'valid_from'  => $validFrom,
            'valid_until' => $validUntil,
            'notes'       => trim((string) $this->request->getPost('notes')) ?: null,
            'created_by'  => auth()->id(),
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ];
    }

    private function normalizeTime(string $t): ?string
    {
        $t = trim($t);
        if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $t)) {
            return substr($t, 0, 5) . ':00';
        }
        return null;
    }

    private function isValidDate(string $d): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
    }
}
