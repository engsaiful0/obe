@if($damages->count() > 0)
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div>
            <p class="text-muted mb-0">
                Showing {{ $damages->firstItem() }} to {{ $damages->lastItem() }} of {{ $damages->total() }} results
            </p>
        </div>
        <div>
            {{ $damages->appends(request()->query())->links() }}
        </div>
    </div>
@endif

