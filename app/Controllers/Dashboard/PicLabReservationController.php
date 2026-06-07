<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Models\LabReservationModel;
use App\Models\LaboratoryModel;

class PicLabReservationController extends BaseController
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
        $this->model    = new LabReservationModel();
        $this->labModel = new LaboratoryModel();
    }

    public function index()
    {
        if ($redirect = $this->ensurePic()) {
            return $redirect;
        }

        $labIds = $this->picLabIds();
        if ($labIds === []) {
            return view('dashboard/pic/reservations/index', [
                'title'        => 'Lab Reservations',
                'reservations' => [],
                'labs'         => [],
                'dayNames'     => $this->dayNames,
            ]);
        }

        $filters = [
            'lab_id' => (int) $this->request->getGet('lab_id'),
            'type'   => trim((string) $this->request->getGet('type')),
        ];
        if (! in_array($filters['type'], ['manual', 'class'], true)) {
            $filters['type'] = '';
        }
        if ($filters['lab_id'] > 0 && ! in_array($filters['lab_id'], $labIds, true)) {
            $filters['lab_id'] = 0;
        }

        $builder = $this->model
            ->select('lab_reservations.*, laboratories.name AS lab_name')
            ->join('laboratories', 'laboratories.id = lab_reservations.lab_id', 'left')
            ->whereIn('lab_reservations.lab_id', $labIds)
            ->orderBy('lab_reservations.created_at', 'DESC');

        if (! empty($filters['lab_id'])) {
            $builder->where('lab_reservations.lab_id', $filters['lab_id']);
        }
        if (! empty($filters['type'])) {
            $builder->where('lab_reservations.type', $filters['type']);
        }

        $reservations = $builder->findAll();
        $labs = $this->labModel->whereIn('id', $labIds)->orderBy('name', 'ASC')->findAll();

        return view('dashboard/pic/reservations/index', [
            'title'        => 'Lab Reservations',
            'reservations' => $reservations,
            'labs'         => $labs,
            'filters'      => $filters,
            'dayNames'     => $this->dayNames,
        ]);
    }

    public function create()
    {
        if ($redirect = $this->ensurePic()) {
            return $redirect;
        }

        $labs = $this->picLabs();
        if ($labs === []) {
            return redirect()->to('/pic/reservations')->with('error', 'No laboratories are assigned to your account.');
        }

        return view('dashboard/pic/reservations/form', [
            'title'    => 'Add Reservation',
            'labs'     => $labs,
            'dayNames' => $this->dayNames,
            'record'   => null,
        ]);
    }

    public function store()
    {
        if ($redirect = $this->ensurePic()) {
            return $redirect;
        }

        $data = $this->buildPayload($this->picLabIds());
        if (is_string($data)) {
            return redirect()->back()->withInput()->with('error', $data);
        }

        $this->model->insert($data);

        return redirect()->to('/pic/reservations')->with('message', 'Reservation added successfully.');
    }

    public function edit(int $id)
    {
        if ($redirect = $this->ensurePic()) {
            return $redirect;
        }

        $record = $this->reservationForPic($id);
        if (! $record) {
            return redirect()->to('/pic/reservations')->with('error', 'Reservation not found.');
        }

        return view('dashboard/pic/reservations/form', [
            'title'    => 'Edit Reservation',
            'labs'     => $this->picLabs(),
            'dayNames' => $this->dayNames,
            'record'   => $record,
        ]);
    }

    public function update(int $id)
    {
        if ($redirect = $this->ensurePic()) {
            return $redirect;
        }

        if (! $this->reservationForPic($id)) {
            return redirect()->to('/pic/reservations')->with('error', 'Reservation not found.');
        }

        $data = $this->buildPayload($this->picLabIds());
        if (is_string($data)) {
            return redirect()->back()->withInput()->with('error', $data);
        }

        $this->model->update($id, $data);

        return redirect()->to('/pic/reservations')->with('message', 'Reservation updated successfully.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->ensurePic()) {
            return $redirect;
        }

        if (! $this->reservationForPic($id)) {
            return redirect()->to('/pic/reservations')->with('error', 'Reservation not found.');
        }

        $this->model->delete($id);

        return redirect()->to('/pic/reservations')->with('message', 'Reservation deleted.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function ensurePic()
    {
        if (! auth()->loggedIn() || ! auth()->user()->inGroup('pic')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }
        return null;
    }

    protected function picLabIds(): array
    {
        $email = strtolower(trim((string) auth()->user()->email));
        return array_column(
            $this->labModel->where('LOWER(TRIM(pic_email)) =', $email)->findAll(),
            'id'
        );
    }

    protected function picLabs(): array
    {
        $email = strtolower(trim((string) auth()->user()->email));
        return $this->labModel->where('LOWER(TRIM(pic_email)) =', $email)
                              ->orderBy('name', 'ASC')
                              ->findAll();
    }

    protected function reservationForPic(int $id): ?array
    {
        $labIds = $this->picLabIds();
        if ($labIds === []) {
            return null;
        }
        $row = $this->model->find($id);
        if (! $row || ! in_array((int) $row['lab_id'], $labIds, true)) {
            return null;
        }
        return $row;
    }

    private function buildPayload(array $allowedLabIds): array|string
    {
        $recurrence = $this->request->getPost('recurrence');
        if (! in_array($recurrence, ['none', 'weekly'], true)) {
            return 'Invalid recurrence type.';
        }

        $labId = (int) $this->request->getPost('lab_id');
        if ($labId <= 0 || ! in_array($labId, $allowedLabIds, true)) {
            return 'You can only manage reservations for your assigned laboratory.';
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

        $date       = null;
        $dow        = null;
        $validFrom  = null;
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
