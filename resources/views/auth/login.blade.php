<!DOCTYPE html>
<html lang="en">
<head>
    <title>FCZCNYX</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="max-h-screen">
<section class="bg-gray-200 min-h-screen flex items-center justify-center">
    <div class="bg-white p-5 flex rounded-2xl shadow-lg max-w-3xl w-full">
        <div class="md:w-1/2 px-5">
            {{-- Display session errors --}}
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6" id="loginForm">
                @csrf
                <div>
                    <label for="employee_code" class="block text-gray-700">Employee Code</label>
                    <input type="text" name="employee_code" id="employee_code" placeholder="Enter Employee Code/ID"
                           class="w-full px-4 py-3 rounded-lg bg-gray-200 mt-2 border focus:border-blue-500 focus:bg-white focus:outline-none"
                           value="{{ old('employee_code') }}">
                    @error('employee_code')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label for="password" class="block text-gray-700">Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter Password"
                           class="w-full px-4 py-3 rounded-lg bg-gray-200 mt-2 border focus:border-blue-500 focus:bg-white focus:outline-none">
                    @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full block bg-blue-500 hover:bg-blue-400 text-white font-semibold rounded-lg px-4 py-3 mt-6">
                    Log In
                </button>
            </form>
        </div>
        <div class="w-1/2 md:block hidden">
            <img src="{{ asset('images/fczcnyx.png') }}" class="rounded-2xl w-full h-full object-cover" alt="page img">
        </div>
    </div>
</section>
</body>
</html>
