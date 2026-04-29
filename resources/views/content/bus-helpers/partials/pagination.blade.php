@if($busHelpers->hasPages())
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $busHelpers->firstItem() }} to {{ $busHelpers->lastItem() }} of {{ $busHelpers->total() }} results
                </div>
                <div>
                    {{ $busHelpers->links() }}
                </div>
            </div>
        </div>
    </div>
@endif

