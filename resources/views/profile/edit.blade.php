@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 space-y-6">
    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
        <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">Profile Information</h2>
        <div class="max-w-xl">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
        <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">Change Password</h2>
        <div class="max-w-xl">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
        <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">Delete Account</h2>
        <div class="max-w-xl">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
