<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // TODO: REMOVE THIS COMMENT FOR PRODUCTION - Domain check disabled for testing
            // Check if email domain is authorized
            // $authorizedDomains = ['@villacollege.edu.mv', '@students.villacollege.edu.mv'];
            // $isAuthorized = false;
            
            // foreach ($authorizedDomains as $domain) {
            //     if (str_ends_with($googleUser->email, $domain)) {
            //         $isAuthorized = true;
            //         break;
            //     }
            // }
            
            // if (!$isAuthorized) {
            //     return redirect()->route('login')
            //         ->with('error', 'Access denied. Only @villacollege.edu.mv and @students.villacollege.edu.mv email addresses are allowed.');
            // }
            
            // Find or create user
            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'email_verified_at' => now(),
                ]
            );
            
            Auth::login($user, true);
            
            return redirect()->route('chat');
            
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Authentication failed. Please try again.');
        }
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
