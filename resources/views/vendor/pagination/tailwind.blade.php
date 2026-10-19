@if ($paginator->hasPages() || $paginator->total() > 0)
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs w-full">

        <!-- Mobile view: Per-page selector + Simple Prev & Next -->
        <div class="flex flex-col gap-2 w-full sm:hidden">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="text-xs text-slate-500 font-medium">Tampilkan:</span>
                    <select onchange="window.location.href = this.value"
                        class="text-xs py-1 px-2 bg-white border border-slate-300 rounded font-bold text-slate-700 shadow-2xs">
                        @foreach ([10, 25, 50, 100] as $size)
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}"
                                {{ request('per_page', $paginator->perPage()) == $size ? 'selected' : '' }}>
                                {{ $size }} data
                            </option>
                        @endforeach
                    </select>
                </div>
                <span class="text-xs text-slate-500">
                    Total: <strong class="text-slate-900">{{ $paginator->total() }}</strong>
                </span>
            </div>

            <div class="flex items-center justify-between w-full pt-2 border-t border-slate-200/80">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-400 bg-white border border-slate-200 rounded cursor-not-allowed">
                        &laquo; Sebelumnya
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-100 hover:text-slate-900 transition">
                        &laquo; Sebelumnya
                    </a>
                @endif

                <span class="text-xs text-slate-600 font-medium">
                    Hal. {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}
                </span>

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-100 hover:text-slate-900 transition">
                        Berikutnya &raquo;
                    </a>
                @else
                    <span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-400 bg-white border border-slate-200 rounded cursor-not-allowed">
                        Berikutnya &raquo;
                    </span>
                @endif
            </div>
        </div>

        <!-- Desktop view: Per Page Selector & Summary on Left, Page Numbers on Right -->
        <div class="hidden sm:flex sm:items-center sm:justify-between w-full gap-4">
            <div class="flex items-center gap-3.5 flex-wrap">
                <!-- Dropdown Baris Per Halaman -->
                <div class="flex items-center gap-1.5">
                    <span class="text-xs text-slate-500 font-medium">Tampilkan</span>
                    <select onchange="window.location.href = this.value"
                        class="text-xs py-1 px-2.5 bg-white border border-slate-300 rounded font-bold text-slate-700 hover:border-slate-400 focus:outline-none focus:ring-1 focus:ring-[#d92534] cursor-pointer shadow-2xs">
                        @foreach ([10, 25, 50, 100] as $size)
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}"
                                {{ request('per_page', $paginator->perPage()) == $size ? 'selected' : '' }}>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-xs text-slate-500 font-medium">data per halaman</span>
                </div>

                <div class="h-4 w-px bg-slate-300"></div>

                <!-- Info Jumlah Data -->
                <p class="text-xs text-slate-500">
                    Menampilkan
                    @if ($paginator->firstItem())
                        <span class="font-bold text-slate-800">{{ $paginator->firstItem() }}</span>
                        sampai
                        <span class="font-bold text-slate-800">{{ $paginator->lastItem() }}</span>
                    @else
                        <span class="font-bold text-slate-800">{{ $paginator->count() }}</span>
                    @endif
                    dari total
                    <span class="font-bold text-slate-900">{{ $paginator->total() }}</span>
                    data
                </p>
            </div>

            <!-- Page Navigation Buttons (Right Side) -->
            @if ($paginator->hasPages())
                <div>
                    <span class="inline-flex items-center gap-1">
                        {{-- Previous Page Link --}}
                        @if ($paginator->onFirstPage())
                            <span aria-disabled="true" aria-label="Sebelumnya">
                                <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium text-slate-300 bg-white border border-slate-200 rounded cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </span>
                            </span>
                        @else
                            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-100 hover:text-slate-900 transition" aria-label="Sebelumnya">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </a>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <span aria-disabled="true">
                                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium text-slate-400 bg-white border border-slate-200 rounded cursor-default">{{ $element }}</span>
                                </span>
                            @endif

                            {{-- Array Of Links --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $paginator->currentPage())
                                        <span aria-current="page">
                                            <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-white bg-[#d92534] border border-[#d92534] rounded shadow-xs cursor-default">{{ $page }}</span>
                                        </span>
                                    @else
                                        <a href="{{ $url }}" class="inline-flex items-center justify-center w-8 h-8 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-100 hover:text-slate-900 transition" aria-label="Ke halaman {{ $page }}">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($paginator->hasMorePages())
                            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-100 hover:text-slate-900 transition" aria-label="Berikutnya">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @else
                            <span aria-disabled="true" aria-label="Berikutnya">
                                <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium text-slate-300 bg-white border border-slate-200 rounded cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </span>
                            </span>
                        @endif
                    </span>
                </div>
            @else
                <div>
                    <span class="inline-flex items-center gap-1">
                        <span aria-disabled="true" aria-label="Sebelumnya">
                            <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium text-slate-300 bg-white border border-slate-200 rounded cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </span>
                        </span>
                        <span aria-current="page">
                            <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-white bg-[#d92534] border border-[#d92534] rounded shadow-xs cursor-default">1</span>
                        </span>
                        <span aria-disabled="true" aria-label="Berikutnya">
                            <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium text-slate-300 bg-white border border-slate-200 rounded cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </span>
                    </span>
                </div>
            @endif
        </div>

    </nav>
@endif
