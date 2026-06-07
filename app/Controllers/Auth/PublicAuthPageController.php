<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class PublicAuthPageController extends BaseController
{
    public function loginView(): RedirectResponse|string
    {
        if (auth()->loggedIn()) {
            return redirect()->to(config('Auth')->loginRedirect());
        }

        return view('auth/login_page');
    }

    public function registerView(): RedirectResponse|string
    {
        if (auth()->loggedIn()) {
            return redirect()->to(config('Auth')->registerRedirect());
        }

        if (! setting('Auth.allowRegistration')) {
            return redirect()->back()->withInput()->with('error', lang('Auth.registerDisabled'));
        }

        return view('auth/register_page');
    }
}
