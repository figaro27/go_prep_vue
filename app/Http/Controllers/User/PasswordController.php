<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;
use Validator;

class PasswordController extends UserController
{
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UserDetail  $userDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current' => ['required', 'string'],
            'new' => 'required|string|min:6|confirmed'
        ]);

        $currentHash = $this->user->password;

        $validator->after(function (
            \Illuminate\Validation\Validator $validator
        ) use ($request, $currentHash) {
            if ($currentHash !== bcrypt($request->get('current'))) {
                $validator
                    ->errors()
                    ->add('current', 'Your current password doesn\'t match');
            }
        });

        $validator->validate($request);
    }
}
