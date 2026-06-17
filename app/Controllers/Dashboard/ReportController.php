<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Services\ReportAnalyticsService;
use Dompdf\Dompdf;
use Throwable;
use InvalidArgumentException;
use RuntimeException;

class ReportController extends BaseController
{
    protected ReportAnalyticsService $reports;

    public function __construct()
    {
        helper('auth');
        $this->reports = new ReportAnalyticsService();
    }

    public function download()
    {
        $report = $this->buildReport();
        if (! is_array($report)) {
            return $report;
        }

        try {
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
        } catch (Throwable $e) {
            log_message('error', 'PDF render failed [{class}] {message} in {file}:{line}', [
                'class'   => get_class($e),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->to('/dashboard/reports/analytics')
                ->with('error', 'The PDF could not be generated. Please try again or contact support.');
        }
    }

    public function downloadCsv()
    {
        $report = $this->buildReport();
        if (! is_array($report)) {
            return $report;
        }

        try {
            return $this->response
                ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $this->reports->csvFilename($report) . '"')
                ->setBody($this->reports->buildCsv($report));
        } catch (Throwable $e) {
            log_message('error', 'CSV build failed [{class}] {message} in {file}:{line}', [
                'class'   => get_class($e),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->to('/dashboard/reports/analytics')
                ->with('error', 'The CSV could not be generated. Please try again or contact support.');
        }
    }

    private function buildReport()
    {
        if (! auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        try {
            return $this->reports->build(auth()->user(), $this->request->getGet());
        } catch (InvalidArgumentException | RuntimeException $e) {
            return redirect()
                ->to('/dashboard/reports/analytics')
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            log_message('error', 'Report build failed [{class}] {message} in {file}:{line}', [
                'class'   => get_class($e),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->to('/dashboard/reports/analytics')
                ->with('error', 'The report could not be generated right now. Please try again later.');
        }
    }
}
