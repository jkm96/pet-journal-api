<?php

namespace App\Services\Admin;

use App\Utils\Constants\AppConstants;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;

class ManageUserService
{
    public function getRegisteredBuyers()
    {
        $buyers = User::orderBy('created_at','desc')
            ->paginate(AppConstants::$pagination);

        return view('admin.buyers.index', compact('buyers'));
    }

    public function getApprovedBuyers()
    {
        $buyers = User::where('is_approved',1)
            ->orderBy('created_at','desc')
            ->paginate(AppConstants::$pagination);

        return view('admin.buyers.approved', compact('buyers'));
    }

    public function getPendingbuyers()
    {
        $buyers = User::where('is_approved',0)
            ->orderBy('created_at','desc')
            ->paginate(AppConstants::$pagination);

        return view('admin.buyers.pending', compact('buyers'));
    }

    public function approvebuyers($buyerId)
    {
        $buyer = User::where('id',$buyerId)->first();

        if ($buyer->is_approved == 0) {
            $message = "approved";
            $buyer->is_approved = 1;
        }else {
            $message = "dis-approved";
            $buyer->is_approved = 0;
        }

        $buyer->approved_at = Carbon::now();
        $buyer->update();

        return redirect()->route('admin.buyers')->with('success',$buyer->email.' '.$message.' successfully');
    }

}
