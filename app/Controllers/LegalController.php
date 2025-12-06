<?php

namespace App\Controllers;

use App\Core\Controller;

class LegalController extends Controller
{
    public function cgu()
    {
        // View for CGU
        $this->view('legal/cgu');
    }
}
