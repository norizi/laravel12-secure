@extends(auth()->check() ? 'layouts.admin' : 'layouts.app')


@section('content')

    {{-- Added mt-12 for top spacing on the main container --}}
    <div class="min-h-[80vh] flex items-center justify-center relative overflow-hidden rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 animate-enter mt-12">
       
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute bottom-[-10%] left-[-10%] w-[50%] h-[50%] bg-blue-500/10 rounded-full blur-[100px]"></div>
            <div class="absolute top-[-10%] right-[-10%] w-[50%] h-[50%] bg-purple-500/10 rounded-full blur-[100px]"></div>
        </div>

        <div class="relative z-10 max-w-lg w-full px-6 text-center mt-12">
            
            <div class="mx-auto w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center mb-6 animate-spin-slow">
                <i data-lucide="settings" class="w-12 h-12 text-blue-500"></i>
            </div>

            <h1 class="text-6xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">503</h1>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-4">Sedang Diselenggara</h2>
            <p class="text-slate-500 dark:text-slate-400 mb-8 leading-relaxed">
                Kami sedang melakukan beberapa kemaskini penting pada sistem. Kami akan kembali beroperasi sebentar lagi. Terima kasih atas kesabaran anda.
            </p>

            <div class="flex justify-center">
                 <button onclick="location.reload()" class="px-6 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold text-sm hover:bg-slate-100 dark:hover:bg-slate-800 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="refresh-ccw" class="w-4 h-4"></i>
                    Semak Status
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
        .animate-spin-slow { animation: spin 4s linear infinite; }
        @keyframes enter { to { opacity: 1; transform: translateY(0); } }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>

@endsection