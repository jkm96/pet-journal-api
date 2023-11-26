<?php

namespace App\Services\User;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class PortalService
{

    /**
     * @return Application|Factory|View
     */
    public function showPortal()
    {
        return view('buyer.portal.portal');
    }

}
