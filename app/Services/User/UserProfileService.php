<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class UserProfileService
{
    /**
     * @return Application|Factory|View
     */
    public function profileEditPage()
    {
        $buyer = User::where('id', Auth::user()->getAuthIdentifier())->first();

        return view('buyer.profile.edit')->with('buyer',$buyer);
    }

    /**
     * @return Application|Factory|View
     */
    public function viewProfile()
    {
        $buyer = User::where('id', Auth::user()->getAuthIdentifier())->first();

        return view('buyer.profile.view')->with('buyer',$buyer);
    }


    /**
     * @param $request
     * @param $id
     * @return RedirectResponse
     */
    public function updateBuyerProfileDetails($request, $id)
    {
        $request->validate([
            'username'=>'required|min:5',
            'id_number'=>'required|min:5',
            'phone_number'=>'required|min:10',
            'first_name'=>'required|string',
            'last_name'=>'required|string'
        ]);

        $buyer = User::where('id',$id)->first();
        if ($request->hasFile('profile_image')){
            $imageName = str_replace(' ', '_',$buyer->username).'.'.$request->profile_image->extension();
            $request->profile_image->move(public_path('profile_pictures'), $imageName);
            File::delete($buyer->profile_url);

            $buyer->profile_url = $imageName;
        }
        $buyer->is_profile_complete = true;
        $buyer->update($request->all());

        return redirect()->route('buyer.profile.view')
            ->with('success', 'Details updated successfully');
    }
}
