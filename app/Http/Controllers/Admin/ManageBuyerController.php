<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ManageBuyerService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ManageBuyerController extends Controller
{

    /**
     * @var ManageBuyerService
     */
    private $_buyerService;

    public function __construct(ManageBuyerService $buyerService)
    {
        $this->middleware('auth:admin');
        $this->_buyerService = $buyerService;
    }

    /**
     * Get a list of all registered Buyers
     *
     * @return Application|Factory|View
     */
    public function getBuyers()
    {
        return $this->_buyerService->getRegisteredBuyers();
    }

     /**
     * Get a list of all registered and approved Buyers
     *
     * @return Application|Factory|View
     */
    public function getApprovedBuyers()
    {
        return $this->_buyerService->getApprovedBuyers();
    }

     /**
     * Get a list of all registered and pending(un-approved) Buyers
     *
     * @return Application|Factory|View
     */
    public function getPendingBuyers()
    {
        return $this->_buyerService->getPendingBuyers();
    }

    /**
     * Approve a Buyer once they are registered
     *
     * @param $id
     * @return RedirectResponse
     */
    public function approveBuyer($id)
    {
        return $this->_buyerService->approveBuyers($id);
    }
}
