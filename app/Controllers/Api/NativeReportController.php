<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\ReportAnalyticsService;
use CodeIgniter\Shield\Entities\User;
use Dompdf\Dompdf;
use InvalidArgumentException;
use RuntimeException;

class NativeReportController extends BaseController
{
    protected ReportAnalyticsService $reports;

    public function __construct()
    {
        helper('auth');
        $this->reports = new ReportAnalyticsService();
    }

    public function show()
    {
        $user = $this->authorizedUser();
        if (! $user instanceof User) {
            return $user;
        }

        try {
            $report = $this->reports->build($user, $this->request->getGet());
        } catch (InvalidArgumentException $e) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ]);
        } catch (RuntimeException $e) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'report' => $report,
            'exports' => [
                'pdf_url' => base_url('/api/native/reports/export/pdf') . $this->queryString(),
                'csv_url' => base_url('/api/native/reports/export/csv') . $this->queryString(),
            ],
        ]);
    }

    public function downloadPdf()
    {
        $user = $this->authorizedUser();
        if (! $user instanceof User) {
            return $user;
        }

        try {
            $report = $this->reports->build($user, $this->request->getGet());
        } catch (InvalidArgumentException $e) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ]);
        } catch (RuntimeException $e) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ]);
        }

        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml(view('reports/summary_pdf', ['report' => $report]));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('Helvetica', 'normal');
        $canvas->page_text(500, 820, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 9, [0.4, 0.45, 0.55]);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $this->reports->pdfFilename($report) . '"')
            ->setBody($dompdf->output());
    }

    public function downloadCsv()
    {
        $user = $this->authorizedUser();
        if (! $user instanceof User) {
            return $user;
        }

        try {
            $report = $this->reports->build($user, $this->request->getGet());
        } catch (InvalidArgumentException $e) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ]);
        } catch (RuntimeException $e) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ]);
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $this->reports->csvFilename($report) . '"')
            ->setBody($this->reports->buildCsv($report));
    }

    protected function authorizedUser()
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Unauthenticated.',
                ]);
        }

        if (! $user->inGroup('pic') && ! $user->inGroup('manager') && ! $user->inGroup('admin')) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'You do not have access to reports.',
                ]);
        }

        return $user;
    }

    private function queryString(): string
    {
        $query = http_build_query(array_filter(
            $this->request->getGet(),
            static fn($value): bool => $value !== null && $value !== ''
        ));

        return $query !== '' ? '?' . $query : '';
    }
}
