<tr>
    <td>{{ $rowIndex ?? 0 + 1 }}</td>
    <td>
        <select class="form-select item-select select2" id="item_select_{{ $rowIndex ?? 0 }}" name="items[{{ $rowIndex ?? 0 }}][item_id]">
            <option value="">Select Item</option>
            @foreach ($items as $item)
                <option value="{{ $item->id }}">{{ $item->item_name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="number" class="form-control quantity-input" name="items[{{ $rowIndex ?? 0 }}][quantity]" step="1" min="1" placeholder="Quantity" />
    </td>
   
    <td>
        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-item-btn">
            <i class="ti ti-trash"></i>
        </button>
    </td>
</tr>