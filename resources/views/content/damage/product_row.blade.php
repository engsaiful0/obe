@php($i = $rowIndex ?? 0)
<tr class="damage-item-row">
  <td class="serial">{{ $i + 1 }}</td>
  <td>
    <select class="form-select item-select" name="items[{{ $i }}][item_id]">
      <option value="">Select Item</option>
      @foreach ($items as $item)
        <option value="{{ $item->id }}">{{ $item->item_name }}</option>
      @endforeach
    </select>
  </td>
  <td>
    <input type="number" step="0.01" min="0.01" class="form-control quantity-input" name="items[{{ $i }}][quantity]" placeholder="0.00">
  </td>
  <td>
    <input type="text" class="form-control reason-input" name="items[{{ $i }}][reason]" placeholder="Reason (optional)">
  </td>
  <td>
    <input type="number" step="0.01" min="0" class="form-control approximate-input" name="items[{{ $i }}][approximate]" placeholder="0.00">
  </td>
  <td>
    <button type="button" class="btn btn-sm btn-danger remove-row-btn"><i class="ti ti-trash"></i></button>
  </td>
</tr>


