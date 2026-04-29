<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'memo_no',
        'bill_no',
        'expense_head_id',
        'user_id',
        'expense_date',
        'remarks',
        'amount',
        'supplier_id',
        'bus_sub_type_id',
        'bus_id',
        'employee_id',
        
    ];

    public function expenseHead()
    {
        return $this->belongsTo(ExpenseHead::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function busSubType()
    {
        return $this->belongsTo(BusSubType::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

}
