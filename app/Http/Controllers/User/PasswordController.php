<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;
use Validator;
use Illuminate\Support\Facades\Hash;

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

        $validator->validate($request);

        $currentPassword = $this->user->password;
        $verifiedCurrentPassword = $request->get('current');
        $newPassword = bcrypt($request->get('new'));

        // If the current password entered matches
        if (Hash::check($verifiedCurrentPassword, $currentPassword)) {
            if (count($validator->errors()) === 0) {
                $this->user->update(['password' => $newPassword]);
            }
        } else {
            $validator
                ->errors()
                ->add('errors', 'Your current password doesn\'t match');
            throw new \Exception($validator->errors()->messages()['errors'][0]);
        }
    }
}
