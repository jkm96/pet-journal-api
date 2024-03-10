<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Buyer\PortalService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class PortalController extends Controller
{
    /**
     * @var PortalService
     */
    private $_portalService;

    public function __construct(PortalService $portalService)
    {
        $this->middleware('auth');
        $this->_portalService = $portalService;
    }

    /**
     * Show the portal dashboard
     *
     * @return Application|Factory|View
     */
    public function showPortalDashboard()
    {
        return $this->_portalService->showPortal();
    }

}
