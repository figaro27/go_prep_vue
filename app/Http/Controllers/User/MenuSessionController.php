<?php

namespace App\Http\Controllers\User;

use App\MenuSession;
use Illuminate\Http\Request;
use App\StoreDetail;
use App\UserDetail;
use Illuminate\Support\Carbon;

class MenuSessionController extends UserController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $storeId = $request->get('store_id');
        $storeName = StoreDetail::where('store_id', $storeId)
            ->pluck('name')
            ->first();
        $userId = $request->get('user_id');
        $userDetail = UserDetail::where('user_id', $userId)->first();
        $userName = $userDetail->firstname . ' ' . $userDetail->lastname;

        // If a menu session was created within the last hour, don't create
        $recentMenuSessions = MenuSession::where('user_id', $userId)
            ->where(
                'created_at',
                '>',
                Carbon::now()
                    ->subHours(1)
                    ->toDateTimeString()
            )
            ->get();

        if (count($recentMenuSessions) > 0) {
            return;
        }

        $menuSession = new MenuSession();
        $menuSession->store_id = $storeId;
        $menuSession->store_name = $storeName;
        $menuSession->user_id = $userId;
        $menuSession->user_name = $userName;
        $menuSession->save();

        return $menuSession;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\MenuSession  $menuSession
     * @return \Illuminate\Http\Response
     */
    public function show(MenuSession $menuSession)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\MenuSession  $menuSession
     * @return \Illuminate\Http\Response
     */
    public function edit(MenuSession $menuSession)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\MenuSession  $menuSession
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MenuSession $menuSession)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\MenuSession  $menuSession
     * @return \Illuminate\Http\Response
     */
    public function destroy(MenuSession $menuSession)
    {
        //
    }
}
