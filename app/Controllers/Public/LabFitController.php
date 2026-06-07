<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Libraries\LabWorkRecommendationService;
use App\Libraries\UploadedDocumentTextExtractor;
use CodeIgniter\HTTP\ResponseInterface;

class LabFitController extends BaseController
{
    protected UploadedDocumentTextExtractor $extractor;
    protected LabWorkRecommendationService $recommendationService;

    public function __construct()
    {
        helper('auth');
        $this->extractor = new UploadedDocumentTextExtractor();
        $this->recommendationService = new LabWorkRecommendationService();
    }

    public function index(): string
    {
        return $this->renderPage();
    }

    public function suggest(): ResponseInterface|string
    {
        $chemicalsInvolved = strtolower(trim((string) $this->request->getPost('chemicals_involved'))) === 'yes';

        $rules = [
            'chemicals_involved' => 'required|in_list[yes,no]',
            'work_document' => 'uploaded[work_document]|max_size[work_document,10240]|ext_in[work_document,doc,docx,pdf,txt,html,htm,md,rtf]',
        ];

        if ($chemicalsInvolved) {
            $rules['sds_document'] = 'uploaded[sds_document]|max_size[sds_document,15360]|ext_in[sds_document,doc,docx,pdf,txt,html,htm,md,rtf]';
        }

        if (! $this->validate($rules)) {
            return $this->response
                ->setStatusCode(422)
                ->setBody($this->renderPage([
                    'formErrors' => $this->validator->getErrors(),
                    'selectedChemicals' => $chemicalsInvolved ? 'yes' : 'no',
                ]));
        }

        $workDocument = $this->request->getFile('work_document');
        $sdsDocument = $chemicalsInvolved ? $this->request->getFile('sds_document') : null;

        try {
            $workExtraction = $this->extractor->extract($workDocument);
            $sdsExtraction = null;

            if ($chemicalsInvolved && $sdsDocument !== null && $sdsDocument->isValid()) {
                $sdsExtraction = $this->extractor->extract($sdsDocument);
            }

            $result = $this->recommendationService->recommend(
                (string) ($workExtraction['text'] ?? ''),
                $sdsExtraction['text'] ?? null,
                $chemicalsInvolved
            );
        } catch (\Throwable $e) {
            log_message('error', 'Lab-fit recommendation failed: ' . $e->getMessage());

            return $this->response
                ->setStatusCode(422)
                ->setBody($this->renderPage([
                    'formErrors' => [
                        'work_document' => 'The uploaded files could not be processed. Please use the provided template and upload a readable DOC, DOCX, PDF, TXT, HTML, or RTF file.',
                    ],
                    'selectedChemicals' => $chemicalsInvolved ? 'yes' : 'no',
                ]));
        }

        return $this->renderPage([
            'selectedChemicals' => $chemicalsInvolved ? 'yes' : 'no',
            'recommendationResult' => $result,
            'documentSummary' => [
                'work_document' => [
                    'name' => (string) ($workExtraction['original_name'] ?? ''),
                    'extension' => (string) ($workExtraction['extension'] ?? ''),
                    'char_count' => (int) ($workExtraction['char_count'] ?? 0),
                    'line_count' => (int) ($workExtraction['line_count'] ?? 0),
                ],
                'sds_document' => $sdsExtraction
                    ? [
                        'name' => (string) ($sdsExtraction['original_name'] ?? ''),
                        'extension' => (string) ($sdsExtraction['extension'] ?? ''),
                        'char_count' => (int) ($sdsExtraction['char_count'] ?? 0),
                        'line_count' => (int) ($sdsExtraction['line_count'] ?? 0),
                    ]
                    : null,
            ],
        ]);
    }

    public function downloadSopTemplate(): ResponseInterface
    {
        return $this->templateResponse(
            'SLAMS_SOP_Template.doc',
            view('public/lab_fit/templates/sop_doc')
        );
    }

    public function downloadSwpTemplate(): ResponseInterface
    {
        return $this->templateResponse(
            'SLAMS_SWP_Template.doc',
            view('public/lab_fit/templates/swp_doc')
        );
    }

    protected function templateResponse(string $filename, string $body): ResponseInterface
    {
        return $this->response
            ->setHeader('Content-Type', 'application/msword; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
            ->setBody($body);
    }

    protected function renderPage(array $data = []): string
    {
        return view('public/lab_fit/index', array_merge([
            'selectedChemicals' => 'no',
            'formErrors' => [],
            'recommendationResult' => null,
            'documentSummary' => null,
        ], $data));
    }
}
