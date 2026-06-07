<div class="import-reference-grid">
    <div class="import-reference-block">
        <h3>Category IDs</h3>
        <div class="responsive-table">
            <table class="advanced-table import-reference-table">
                <thead><tr><th>ID</th><th>Category</th><th>Code</th></tr></thead>
                <tbody>
                    @forelse($assetCategories as $category)
                        <tr><td>{{ $category->id }}</td><td>{{ $category->name }}</td><td>{{ $category->code ?: '-' }}</td></tr>
                    @empty
                        <tr><td class="table-empty" colspan="3">No active categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="import-reference-block">
        <h3>Brand IDs</h3>
        <div class="responsive-table">
            <table class="advanced-table import-reference-table">
                <thead><tr><th>ID</th><th>Brand</th></tr></thead>
                <tbody>
                    @forelse($assetBrands as $brand)
                        <tr><td>{{ $brand->id }}</td><td>{{ $brand->name }}</td></tr>
                    @empty
                        <tr><td class="table-empty" colspan="2">No active brands found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="import-reference-block">
        <h3>Condition Values</h3>
        <div class="responsive-table">
            <table class="advanced-table import-reference-table">
                <thead><tr><th>Value</th><th>Label</th></tr></thead>
                <tbody>
                    @foreach($assetConditions as $condition)
                        <tr><td>{{ $condition->value }}</td><td>{{ $condition->label() }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
