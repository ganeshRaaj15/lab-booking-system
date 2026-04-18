<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

class DocumentController extends BaseController
{
    public function viewPdf($filename)
    {
        helper('auth');
        $filename = basename((string) $filename);
        if ($filename === '' || ! preg_match('/^[A-Za-z0-9._-]+\.pdf$/i', $filename)) {
            throw new PageNotFoundException('Invalid filename');
        }
        
        // Check if user is logged in
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }
        
        $user = auth()->user();
        
        $bookingModel = new \App\Models\BookingModel();

        // Admins and managers may review all booking documents. PICs are scoped to their assigned labs.
        if (! $user->inGroup('admin') && ! $user->inGroup('manager')) {
            $builder = $bookingModel
                ->select('bookings.id, bookings.user_id, laboratories.pic_email')
                ->join('laboratories', 'laboratories.id = bookings.lab_id', 'left')
                ->where('bookings.pdf_path', '/uploads/pdfs/' . $filename);

            if ($user->inGroup('pic')) {
                $builder->where('LOWER(TRIM(laboratories.pic_email)) =', strtolower(trim((string) $user->email)));
            } else {
                $builder->where('bookings.user_id', auth()->id());
            }

            if (! $builder->first()) {
                return redirect()->back()->with('error', 'Access denied.');
            }
        }
        
        $basePath = realpath(WRITEPATH . 'uploads/pdfs');
        $filePath = realpath(WRITEPATH . 'uploads/pdfs/' . $filename);
        
        if (! $basePath || ! $filePath || ! is_file($filePath) || ! str_starts_with($filePath, $basePath . DIRECTORY_SEPARATOR)) {
            throw new PageNotFoundException('File not found');
        }
        
        // Serve the PDF
        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody(file_get_contents($filePath));
    }
}
