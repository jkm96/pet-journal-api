<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Auth\DashboardService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * @var DashboardService
     */
    private $_dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->middleware('auth:admin');
        $this->_dashboardService = $dashboardService;
    }

    /**
     * Show the admin dashboard
     *
     * @return Application|Factory|View
     */
    public function showDashboard()
    {
        return $this->_dashboardService->renderDashboard();
    }

    public function adminStatistics()
    {

    }
}
