<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

class DocumentController extends BaseController
{
    public function viewPdf($filename)
    {
        helper('auth');
        
        // Check if user is logged in
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }
        
        $user = auth()->user();
        
        // Only allow PIC, Manager, Admin, or the booking owner to view
        if (!$user->inGroup('pic') && !$user->inGroup('manager') && !$user->inGroup('admin')) {
            // Check if user owns the booking
            $bookingModel = new \App\Models\BookingModel();
            $booking = $bookingModel->where('pdf_path', '/uploads/pdfs/' . $filename)
                                    ->where('user_id', auth()->id())
                                    ->first();
            
            if (!$booking) {
                return redirect()->back()->with('error', 'Access denied.');
            }
        }
        
        $filePath = WRITEPATH . 'uploads/pdfs/' . $filename;
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }
        
        // Serve the PDF
        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody(file_get_contents($filePath));
    }
}