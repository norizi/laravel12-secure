@extends(auth()->check() ? 'layouts.admin' : 'layouts.app')


@section('content')

     {{-- Added mt-12 for top spacing on the main container --}}
    <div class="min-h-[80vh] flex items-center justify-center relative overflow-hidden rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 animate-enter mt-12">
      
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[-10%] left-[30%] w-[40%] h-[40%] bg-indigo-500/10 rounded-full blur-[100px]"></div>
        </div>

        <div class="relative z-10 max-w-lg w-full px-6 text-center mt-12">
            
            <div class="mx-auto w-24 h-24 bg-indigo-50 dark:bg-indigo-900/20 rounded-full flex items-center justify-center mb-6">
                <i data-lucide="hourglass" class="w-12 h-12 text-indigo-500"></i>
            </div>

            <h1 class="text-6xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">419</h1>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-4">Halaman Tamat Tempoh</h2>
            <p class="text-slate-500 dark:text-slate-400 mb-8 leading-relaxed">
                Sesi anda telah tamat tempoh kerana ketidakaktifan. Sila muat semula halaman dan cuba hantar borang anda sekali lagi.
            </p>

            <div class="flex justify-center">
                <button onclick="location.reload()" class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 shadow-lg shadow-indigo-500/20 transition-all flex items-center justify-center gap-2 transform hover:-translate-y-1">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Muat Semula Halaman
                </button>
            </div>
            
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>

    <style>
        .animate-enter { animation: enter 0.6s ease-out forwards; opacity: 0; transform: translateY(20px); }
        @keyframes enter { to { opacity: 1; transform: translateY(0); } }
    </style>

@endsection