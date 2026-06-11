@if($reservations->hasPages())
<div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">
    {{ $reservations->appends(request()->query())->links() }}
</div>
@endif
