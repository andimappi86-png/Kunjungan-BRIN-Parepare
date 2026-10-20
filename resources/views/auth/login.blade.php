<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRIN Visit — Masuk Administrator</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-slate-800 font-sans antialiased flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full mx-auto p-8 bg-surface rounded-xl shadow-sm border border-border">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-primary text-white font-bold text-xl mb-3">B</div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">BRIN Visit</h1>
            <p class="text-xs text-slate-500 mt-1">Sistem Informasi Kunjungan Kantor BRIN</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-lg">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Email Administrator</label>
                <input type="email" name="email" value="admin@brin.go.id" required class="w-full rounded-lg border border-border px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Password</label>
                <input type="password" name="password" value="password123" required class="w-full rounded-lg border border-border px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
            </div>
            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-border text-primary focus:ring-primary"> Ingat Saya
                </label>
            </div>
            <button type="submit" class="w-full py-2.5 bg-primary text-white rounded-lg font-medium text-sm hover:bg-blue-700 transition">Masuk Dashboard</button>
        </form>
    </div>
</body>
</html>
