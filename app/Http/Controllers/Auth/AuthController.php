<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Show login form
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle login request
    public function login(Request $request)
    {
        try {
            $request->validate([
                'employee_code' => 'required|string',
                'password' => 'required',
            ]);

            $user = User::where('employee_code', $request->employee_code)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return back()->withErrors(['employee_code' => 'Invalid credentials']);
            }

            Auth::login($user);

            $roleIds = $user->roles->pluck('id')->toArray();

            // Log user role IDs for debugging (optional)
            Log::info('User Role IDs on login: ', $roleIds);

            // If user has dashboard access
            if (array_intersect($roleIds, [37, 38, 39, 40, 41, 42])) {
                return redirect()->route('dashboard');
            }

            // User logged in successfully but has no dashboard access
            return redirect()->route('no.dashboard'); // Define this route in your web.php

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }




    // Show register form
    public function showRegisterForm()
    {
        $roles = Role::all();
        return view('auth.register', compact('roles'));
    }

    // Handle register request
    public function register(Request $request)
    {
        try {
            $request->validate([
                'employee_code' => 'required|unique:users,employee_code',
                'fname' => 'required',
                'lname' => 'required',
                'position' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed',
                'role_id' => 'required|exists:roles,id',
            ]);

            $user = User::create([
                'employee_code' => $request->employee_code,
                'fname' => $request->fname,
                'lname' => $request->lname,
                'position' => $request->position,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->roles()->attach($request->role_id);

            return redirect()->route('login')->with('success', 'Registration successful.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }


    // Handle logout
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
