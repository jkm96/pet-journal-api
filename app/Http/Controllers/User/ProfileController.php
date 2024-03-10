<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Buyer\BuyerProfileService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * @var BuyerProfileService
     */
    private $_memberService;

    public function __construct(BuyerProfileService $memberService)
    {
        $this->middleware('auth');
        $this->_memberService = $memberService;
    }

    /**
     * Show the page to update buyer's personal details
     *
     * @return Application|Factory|View
     */
    public function showProfileEditPage()
    {
        return $this->_memberService->profileEditPage();
    }

    /**
     * Show the profile page with buyer's details
     *
     * @return Application|Factory|View
     */
    public function showProfilePage()
    {
        return $this->_memberService->viewProfile();
    }

    /**
     * Update buyer details
     *
     * @param Request $request
     * @param $buyer_id
     * @return RedirectResponse
     */
    public function updateBuyerProfile(Request $request, $buyer_id)
    {
        return $this->_memberService->updateBuyerProfileDetails($request,$buyer_id);
    }

}
