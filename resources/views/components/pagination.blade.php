@props(['paginator'])
@if ($paginator->hasPages())
    <div class="flex items-center justify-between px-4 py-3 bg-surface border-t border-border sm:px-6">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-400 bg-white border border-border rounded-md cursor-not-allowed">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-border rounded-md hover:bg-slate-50">Previous</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-slate-700 bg-white border border-border rounded-md hover:bg-slate-50">Next</a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-slate-400 bg-white border border-border rounded-md cursor-not-allowed">Next</span>
            @endif
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-slate-700">
                    Showing <span class="font-medium">{{ $paginator->firstItem() }}</span> to <span class="font-medium">{{ $paginator->lastItem() }}</span> of <span class="font-medium">{{ $paginator->total() }}</span> results
                </p>
            </div>
            <div>
                {{ $paginator->links() }}
            </div>
        </div>
    </div>
@endif
