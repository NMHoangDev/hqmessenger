<?php

namespace App\Http\Controllers;

use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class UserProfileController extends Controller
{
    use FileUploadTrait;

    public function update(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'id' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:100'],
            'avatar' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Corrected validation for file
        ]);

        // Assuming uploadFile method handles file storage and returns the path
        $avatarPath = $this->uploadFile($request, 'avatar');

        // For debugging purposes, you can check the avatar path
        // Proceed with other logic such as updating the user profile with the new avatar path

        $user = Auth::user();
        if ($user != null) $user->avatar = $avatarPath;
        $user->name = $request->name;
        $user->id = $request->id;
        $user->email = $request->email;
        if ($request->filled("current_password")) {
            // Xác thực dữ liệu từ request
            $request->validate([
                'current_password' => ['required', 'current_password'], // Kiểm tra mật khẩu hiện tại
                'password' => [
                    'required',
                    'string',
                    Password::min(8)->mixedCase()->numbers()->symbols(), // Độ mạnh của mật khẩu mới
                    'confirmed' // Xác nhận mật khẩu trùng khớp với password_confirmation
                ],
            ]);

            // Cập nhật mật khẩu mới cho người dùng
            $user->password = Hash::make($request->password); // Sử dụng Hash::make() để mã hóa mật khẩu
            // Lưu người dùng với mật khẩu mới
        };
        $user->save();

        notyf()->addSuccess("Updated Successfully");

        return response(["message", "Updated Successfully",], 200);
    }
}
