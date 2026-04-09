@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.14),_transparent_32%),linear-gradient(180deg,#f8fafc_0%,#eef2ff_100%)] py-10">
    <div class="mx-auto max-w-3xl px-4">
        <section class="rounded-3xl border border-white/70 bg-white p-8 shadow-[0_24px_70px_rgba(15,23,42,0.10)]">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-slate-900">Update Credentials</h2>
                    <p class="mt-2 text-sm text-slate-500">Fill out all password fields below, then confirm the change.</p>
                </div>

                @if(session('success'))
                    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                        <i class="fas fa-check-circle mt-0.5"></i>
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-exclamation-circle mt-0.5"></i>
                            <div>
                                <p class="text-sm font-semibold">Please review the password details below.</p>
                                <ul class="mt-2 list-disc pl-5 text-sm">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('password.change') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="current_password" class="mb-2 inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-700">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm">
                                <i class="fas fa-lock text-xs"></i>
                            </span>
                            <span>Current Password</span>
                        </label>
                        <div class="group relative">
                            <input
                                type="password"
                                name="current_password"
                                id="current_password"
                                required
                                class="password-input w-full rounded-2xl border border-slate-300 bg-slate-50 py-3 pl-4 pr-12 text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Enter your current password"
                            >
                            <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" data-target="current_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="new_password" class="mb-2 inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-700">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm">
                                <i class="fas fa-key text-xs"></i>
                            </span>
                            <span>New Password</span>
                        </label>
                        <div class="group relative">
                            <input
                                type="password"
                                name="new_password"
                                id="new_password"
                                required
                                class="password-input w-full rounded-2xl border border-slate-300 bg-slate-50 py-3 pl-4 pr-12 text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Create a new password"
                            >
                            <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" data-target="new_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="mb-2 inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-700">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm">
                                <i class="fas fa-check-double text-xs"></i>
                            </span>
                            <span>Confirm New Password</span>
                        </label>
                        <div class="group relative">
                            <input
                                type="password"
                                name="new_password_confirmation"
                                id="new_password_confirmation"
                                required
                                class="password-input w-full rounded-2xl border border-slate-300 bg-slate-50 py-3 pl-4 pr-12 text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Re-enter your new password"
                            >
                            <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" data-target="new_password_confirmation">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p id="passwordMatchHint" class="mt-2 text-xs text-slate-500">Passwords should match exactly before you submit.</p>
                    </div>

                    <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ url()->previous() }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            <i class="fas fa-arrow-left"></i>
                            Back
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 hover:shadow-blue-600/30">
                            <i class="fas fa-save"></i>
                            Update Password
                        </button>
                    </div>
                </form>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('new_password_confirmation');
    const passwordMatchHint = document.getElementById('passwordMatchHint');

    toggleButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const targetInput = document.getElementById(button.dataset.target);

            if (!targetInput) {
                return;
            }

            const isPassword = targetInput.type === 'password';
            targetInput.type = isPassword ? 'text' : 'password';

            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', !isPassword);
                icon.classList.toggle('fa-eye-slash', isPassword);
            }
        });
    });

    function updatePasswordMatchHint() {
        if (!newPasswordInput || !confirmPasswordInput || !passwordMatchHint) {
            return;
        }

        if (confirmPasswordInput.value.length === 0) {
            passwordMatchHint.textContent = 'Passwords should match exactly before you submit.';
            passwordMatchHint.className = 'mt-2 text-xs text-slate-500';
            return;
        }

        if (newPasswordInput.value === confirmPasswordInput.value) {
            passwordMatchHint.textContent = 'Passwords match and are ready to submit.';
            passwordMatchHint.className = 'mt-2 text-xs font-medium text-emerald-600';
            return;
        }

        passwordMatchHint.textContent = 'Passwords do not match yet.';
        passwordMatchHint.className = 'mt-2 text-xs font-medium text-rose-600';
    }

    newPasswordInput?.addEventListener('input', updatePasswordMatchHint);
    confirmPasswordInput?.addEventListener('input', updatePasswordMatchHint);
});
</script>
@endsection
