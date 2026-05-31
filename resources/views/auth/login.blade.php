<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dynamic PMS V.2</title>
    @vite('resources/css/app.css')
    <link href="{{ asset('assets/fontawesome/css/all.min.css') }}" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
            <div class="text-center mb-8">
                <i class="fas fa-hotel text-4xl text-blue-600"></i>
                <h2 class="text-2xl font-bold mt-2">Dynamic PMS V.2</h2>
                <p class="text-gray-600">Silakan login untuk melanjutkan</p>
            </div>
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Username atau Email</label>
                    <input type="text" name="login" value="{{ old('login') }}" class="w-full border rounded px-3 py-2 @error('login') border-red-500 @enderror" required autofocus>
                    @error('login')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                    Login
                </button>
            </form>
            
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>Demo Account:</p>
                <p>owner@hotel.com / password</p>
                <p>admin@hotel.com / password</p>
                <p>frontoffice@hotel.com / password</p>
            </div>
        </div>
    </div>
</body>
</html>