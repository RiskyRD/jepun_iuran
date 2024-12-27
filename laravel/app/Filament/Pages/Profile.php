<?php
namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $title = 'Profile';
    protected static string $view = 'filament.pages.profile';

    protected static ?string $navigationGroup = 'Personal';

    public $userData;

    // Method to access the page
    public static function canAccess(): bool
    {
        return auth()->check(); // Allow access if the user is logged in
    }

    // Retrieve the authenticated user's data
    public function mount(): void
    {
        $user = Auth::user();

        $this->userData = [
            'name' => $user->name,
            'email' => $user->email,
            'telephone' => $user->telephone,
            'status' => $user->status,
            'gang' => optional($user->gang)->gang_name, // Use optional() for safety
            'role' => $user->roles->pluck('name')->implode(', '), // Assuming you use roles
        ];
    }
}
