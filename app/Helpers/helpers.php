<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('resolvePanelIdFromUser')) {
    function resolvePanelIdFromUser(): string
    {
        $user = Auth::user();

        if (! $user) {
            return 'admin';
        }

        if ($user->hasRole('super_admin')) {
            return 'admin';
        }

        if ($user->hasRole('blog_user')) {
            return 'user';
        }

        return 'admin';
    }
}
