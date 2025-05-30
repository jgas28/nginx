@if ($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

@if (session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
        {{ session('success') }}
    </div>
@endif
<form action="{{ route('register') }}" method="POST">
    @csrf
    <input type="text" name="employee_code" placeholder="Employee Code" required>
    <input type="text" name="fname" placeholder="First Name" required>
    <input type="text" name="lname" placeholder="Last Name" required>
     <input type="text" name="position" placeholder="Position" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
    <select name="role_id" required>
        @foreach($roles as $role)
            <option value="{{ $role->id }}">{{ $role->name }}</option>
        @endforeach
    </select>
    <button type="submit">Register</button>
</form>
