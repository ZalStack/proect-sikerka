@if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 rounded-2xl p-4 flex items-start justify-between gap-3 shadow-sm" role="alert">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                <i class="fa-solid fa-check"></i>
            </div>
            <div>
                <p class="text-sm font-bold">Berhasil</p>
                <p class="text-xs sm:text-sm font-medium mt-0.5 text-emerald-700">{{ session('success') }}</p>
            </div>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 p-1 transition-colors">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="bg-rose-500/10 border border-rose-500/20 text-rose-800 rounded-2xl p-4 flex items-start justify-between gap-3 shadow-sm" role="alert">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <p class="text-sm font-bold">Terjadi Kesalahan</p>
                <p class="text-xs sm:text-sm font-medium mt-0.5 text-rose-700">{{ session('error') }}</p>
            </div>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 p-1 transition-colors">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div class="bg-amber-500/10 border border-amber-500/20 text-amber-800 rounded-2xl p-4 flex items-start justify-between gap-3 shadow-sm" role="alert">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <div>
                <p class="text-sm font-bold">Periksa Kembali Input</p>
                <ul class="text-xs sm:text-sm font-medium mt-0.5 text-amber-700 list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-amber-600 hover:text-amber-900 p-1 transition-colors">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
@endif
