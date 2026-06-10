<?php

namespace App\Http\Controllers;

use App\Services\GoHostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MemberDashboardController extends Controller
{
    public function __construct(private GoHostService $gohost) {}

    public function index()
    {
        $user = Auth::user();

        return view('member.dashboard', compact('user'));
    }

    public function getMyBookings(Request $request): JsonResponse
    {
        $user  = Auth::user();
        $month = $request->input('month', now()->format('Y-m'));

        [$year, $monthNum] = explode('-', $month);
        $startDate = "$year-$monthNum-01";
        $endDate   = date('Y-m-t', strtotime($startDate));

        if (!$this->gohost->isConfigured()) {
            return response()->json(['success' => true, 'data' => [], 'message' => 'GoHost chưa được cấu hình.']);
        }

        $result = $this->gohost->getBookings($startDate, $endDate, 1, 100);

        if (!isset($result['data'])) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Lỗi GoHost.']);
        }

        $userEmail  = strtolower($user->email);
        $myBookings = \array_values(\array_filter($result['data'], function ($b) use ($userEmail) {
            return strtolower($b['customer']['email'] ?? '') === $userEmail;
        }));

        return response()->json(['success' => true, 'data' => $myBookings]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'current_password'      => 'nullable|string',
            'password'              => 'nullable|min:8|confirmed',
            'password_confirmation' => 'nullable',
        ]);

        if (!empty($validated['current_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.'])->withInput();
            }
        }

        $user->name = $validated['name'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('profile_success', 'Cập nhật hồ sơ thành công!');
    }
}
