<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod as PaymentMethodModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PaymentMethod extends Controller
{
  public function index()
  {
    return view('content.settings.payment-method');
  }

  public function getPaymentMethods(Request $request)
  {
    $paymentMethods = PaymentMethodModel::all();
    return response()->json([
      'data' => $paymentMethods,
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'payment_method_name' => 'required|string|max:255|unique:payment_methods,payment_method_name',
    ]);
$user = Auth::user();
        $userId = $user->id;
    $paymentMethod = PaymentMethodModel::create([
      'payment_method_name' => $request->payment_method_name,
      'user_id' => $userId,
    ]);

    return response()->json(['message' => 'Payment method created successfully.', 'data' => $paymentMethod], Response::HTTP_CREATED);
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'payment_method_name' => 'required|string|max:255|unique:payment_methods,payment_method_name,' . $id,
    ]);

    $paymentMethod = PaymentMethodModel::findOrFail($id);
    $paymentMethod->update([
      'payment_method_name' => $request->payment_method_name,
    ]);

    return response()->json(['message' => 'Payment method updated successfully.', 'data' => $paymentMethod]);
  }

  public function destroy($id)
  {
    $paymentMethod = PaymentMethodModel::findOrFail($id);
    $paymentMethod->delete();

    return response()->json(['message' => 'Payment method deleted successfully.']);
  }
}
