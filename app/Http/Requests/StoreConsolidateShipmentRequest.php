<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreConsolidateShipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Foreign Keys
            'carrier_id' => 'nullable|exists:carriers,id',
            'driver_id' => 'nullable|exists:drivers,id',

            // Consolidation Type
            'consolidation_type' => 'required|in:Personal,Commercial,Bulk Order',

            // Customer & Receiver Info
            'consolidated_for' => 'nullable|string|max:255',
            'customer_email' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:255',
            'receiver_name' => 'nullable|string|max:255',
            'receiver_address' => 'nullable|string',
            'receiver_phone' => 'nullable|string|max:255',
            'receiver_email' => 'nullable|string|max:255',

            // Logistics & Routing
            'origin_warehouse' => 'nullable|string|max:255',
            'destination_warehouse' => 'nullable|string|max:255',
            'expected_departure_date' => 'nullable|string',
            'expected_arrival_date' => 'nullable|string',

            // Cost & Payment
            'total_weight' => 'nullable|numeric|min:0',
            'total_shipping_cost' => 'nullable|numeric|min:0',
            'handling_fee' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:Paid,Pending,Partially Paid',
            'payment_method' => 'nullable|in:Cash,Card,Wallet,Transfer,Other',
            // 'accepted_status' => 'required|in:Accepted,Rejected,Pending',
            // 'status' => 'required|string|max:100',

            //chargeTable
            'charge_type.*' => 'nullable|string',
            'comment.*' => 'nullable|string',
            'units.*' => 'nullable|numeric',
            'rate.*' => 'nullable|numeric',
            'amount.*' => 'nullable|numeric',
            'discount.*' => 'nullable|numeric',
            'internal_notes.*' => 'nullable|string',
            'billed.*' => 'nullable|boolean',
            'invoice_number.*' => 'nullable|string',
            'invoice_date.*' => 'nullable|date',
            'total.*' => 'nullable|numeric',
            'net_total.*' => 'nullable|numeric',

            // 'charges' => 'nullable|array',
            // 'charges.*.charge_type' => 'nullable|string|max:255',
            // 'charges.*.comment' => 'nullable|string|max:500',
            // 'charges.*.units' => 'nullable|integer|min:1',
            // 'charges.*.rate' => 'nullable|numeric|min:0',
            // 'charges.*.amount' => 'nullable|numeric|min:0',
            // 'charges.*.discount' => 'nullable|numeric|min:0|max:100',
            // 'charges.*.internal_notes_charges' => 'nullable|string',
            // 'charges.*.billed' => 'nullable|boolean',
            // 'charges.*.invoice_number' => 'nullable|string|unique:invoices,invoice_number|max:50',
            // 'charges.*.invoice_date' => 'nullable|date',
            // 'charges.*.total' => 'nullable|numeric|min:0',
            // 'charges.*.net_total' => 'nullable|numeric|min:0',

            // Documents
            'file_path.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'file_path.*' => 'nullable|file|mimes:pdf,jpg,png,jpeg,doc,docx|max:2048',
            'file_path' => 'sometimes|array',
            'proof_of_delivery_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'invoice_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp',
            'internal_notes' => 'nullable|string',

        ];
    }

    public function messages()
    {
        return [
            'user_id.exists' => 'The selected user does not exist.',
            'branch_id.exists' => 'The selected branch does not exist.',
            'customer_id.exists' => 'The selected customer does not exist.',
            'carrier_id.exists' => 'The selected carrier does not exist.',
            'driver_id.exists' => 'The selected driver does not exist.',

            'consolidation_type.required' => 'Consolidation type is required.',
            'consolidation_type.in' => 'Consolidation type must be Personal, Commercial, or Bulk Order.',

            'payment_status.required' => 'Payment status is required.',
            'payment_status.in' => 'Payment status must be Paid, Pending, or Partially Paid.',

            'payment_method.in' => 'Payment method must be one of: Cash, Card, Wallet, Transfer, or Other.',

            'accepted_status.required' => 'Accepted status is required.',
            'accepted_status.in' => 'Accepted status must be Accepted, Rejected, or Pending.',

            'status.required' => 'Status is required.',
            'status.max' => 'Status cannot exceed 100 characters.',

            'expected_arrival_date.after_or_equal' => 'Arrival date must be after or equal to the departure date.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422));
    }
}
