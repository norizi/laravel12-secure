@extends(auth()->check() ? 'layouts.admin' : 'layouts.app')

@section('content')

    {{-- Added mt-12 for top spacing on the main container --}}
    <div class="min-h-[80vh] flex items-center justify-center relative overflow-hidden rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 animate-enter mt-12">
      
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-rose-500/10 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-red-500/10 rounded-full blur-[100px]"></div>
        </div>

        <div class="relative z-10 max-w-lg w-full px-6 text-center mt-12">
            
            <div class="mx-auto w-24 h-24 bg-rose-50 dark:bg-rose-900/20 rounded-full flex items-center justify-center mb-6">
                <i data-lucide="server-crash" class="w-12 h-12 text-rose-500"></i>
            </div>

            <h1 class="text-6xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">500</h1>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-4">Ralat Pelayan</h2>
            <p class="text-slate-500 dark:text-slate-400 mb-8 leading-relaxed">
                Maaf, sesuatu yang tidak kena berlaku di pihak kami. Pasukan teknikal kami sedang berusaha membaikinya.
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button onclick="history.back()" class="px-6 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold text-sm hover:bg-slate-100 dark:hover:bg-slate-800 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Kembali
                </button>
                 @auth
                <a href="{{ url('/home') }}" class="px-6 py-2.5 rounded-xl bg-rose-600 text-white font-bold text-sm hover:bg-rose-700 shadow-lg shadow-rose-500/20 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="home" class="w-4 h-4"></i>
                    Ke Halaman Utama
                </a>
                @endauth
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