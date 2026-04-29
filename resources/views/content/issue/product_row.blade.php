<tr>
    <td>{{ ($rowIndex ?? 0) + 1 }}</td>
    <td>
        <select class="form-select item-select select2" name="items[{{ $rowIndex ?? 0 }}][item_id]">
            <option value="">Select Item</option>
            @foreach ($items as $item)
            <option value="{{ $item->id }}">{{ $item->item_name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select class="form-select unit-select select2" name="items[{{ $rowIndex ?? 0 }}][unit_id]">
            <option value="">Select Unit</option>
            @foreach ($units as $unit)
            <option value="{{ $unit->id }}">{{ $unit->unit_name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="number" class="form-control quantity-input" name="items[{{ $rowIndex ?? 0 }}][quantity]"
            step="0.01" min="0.01" placeholder="Quantity" />
    </td>
    <td>
        <span class="quantity-display badge bg-info">0.00</span>
    </td>
    <td>
        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-item-btn">
            <i class="ti ti-trash"></i>
        </button>
    </td>
</tr>
