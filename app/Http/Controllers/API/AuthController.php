<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Session\Middleware\StartSession;

class AuthController extends Controller
{


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $loginType = filter_var($request->input('username'), FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        $user = User::where($loginType, $request->input('username'))
            ->where('status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'User Tidak ditemukan atau akun tidak aktif.'
            ], 401);
        }
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Password salah.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load('employee');


        $ipAddress  = $request->ip();
        $userAgent  = $request->userAgent();
        $deviceInfo = $this->parseUserAgent($userAgent);

        $request->session()->regenerate();
        $request->session()->put('user_id',      $user->id);
        $request->session()->put('user_name',    $user->name);
        $request->session()->put('username',     $user->username);
        $request->session()->put('role',         $user->role);
        $request->session()->put('token',        $token);
        $request->session()->put('logged_in_at', now()->toDateTimeString());
        $request->session()->put('ip_address',   $ipAddress);
        $request->session()->put('user_agent',   $userAgent);
        $request->session()->put('device',       $deviceInfo['device']);
        $request->session()->put('browser',      $deviceInfo['browser']);
        $request->session()->put('platform',     $deviceInfo['platform']);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful.',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ]);
    }

    private function parseUserAgent(?string $userAgent): array
    {
        if (!$userAgent) {
            return ['device' => 'Unknown', 'browser' => 'Unknown', 'platform' => 'Unknown'];
        }

        $platform = 'Unknown';
        if (preg_match('/windows/i', $userAgent))        $platform = 'Windows';
        elseif (preg_match('/macintosh|mac os x/i', $userAgent)) $platform = 'MacOS';
        elseif (preg_match('/linux/i', $userAgent))      $platform = 'Linux';
        elseif (preg_match('/android/i', $userAgent))    $platform = 'Android';
        elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) $platform = 'iOS';

        $browser = 'Unknown';
        if (preg_match('/edg/i', $userAgent))            $browser = 'Edge';
        elseif (preg_match('/opr|opera/i', $userAgent))  $browser = 'Opera';
        elseif (preg_match('/chrome/i', $userAgent))     $browser = 'Chrome';
        elseif (preg_match('/safari/i', $userAgent))     $browser = 'Safari';
        elseif (preg_match('/firefox/i', $userAgent))    $browser = 'Firefox';
        elseif (preg_match('/msie|trident/i', $userAgent)) $browser = 'Internet Explorer';

        $device = 'Desktop';
        if (preg_match('/mobile/i', $userAgent))         $device = 'Mobile';
        elseif (preg_match('/tablet|ipad/i', $userAgent)) $device = 'Tablet';

        return compact('platform', 'browser', 'device');
    }

    public function checkToken(Request $request)
    {
        try {
            return response()->json([
                'message' => 'Token is valid',
                'user' => User::where('id', Auth::user()->id)->with('employee')->first(),
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'success' => false,
            ]);
        }
    }
    public function getNotification()
    {
        $notification = getNotification();

        return response()->json($notification);
    }
    public function getCount()
    {
        $count = countNotification();

        return response()->json($count);
    }

    public function isRead($id)
    {
        $n = Notification::findOrFail($id);
        $n->is_read = true;
        $n->save();

        return response()->json([
            'success' => true,
            'message' => 'Read Successfully',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout'
        ], 200);
    }
}
