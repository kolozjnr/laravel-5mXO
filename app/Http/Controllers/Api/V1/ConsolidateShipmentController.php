<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ConsolidateShipment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\ConsolidateShipmentDoc;
use Illuminate\Support\Facades\Validator;
use App\Models\ConsolidateShipmentCharges;
use App\Mail\ConsolidateShipmentCustomerMail;
use App\Mail\ConsolidateShipmentRecieverMail;
use App\Http\Requests\StoreConsolidateShipmentRequest;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Notifications\DriverAcceptConsolidationDeliveryNotification;

class ConsolidateShipmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchId = auth()->user()->getBranchId();
        $consolidateShipments = ConsolidateShipment::where('branch_id', $branchId)
        ->where('user_id', $user->id)
        ->with('customer.user', 'carrier', 'driver.user')
        ->latest()
        ->get();
        return response()->json(['consolidateShipments' => $consolidateShipments], 200);
    }
    public function show($id)
    {
        $user = auth()->user();
        $branchId = auth()->user()->getBranchId();
        
        $consolidateShipment = ConsolidateShipment::where('branch_id', $branchId)
            ->where('id', $id)
            ->with('customer', 'carrier', 'driver', 'documents')
            ->firstOrFail();

        return response()->json(['consolidateShipment' => $consolidateShipment], 200);
    }

    public function store(StoreConsolidateShipmentRequest $request)
    {
       // dd($request->all());
        $validatedData = $request->validated();
        $user = auth()->user();
        $branchId = auth()->user()->getBranchId();
        $branch = $user->branch()->with('user')->first();
        $creatorDriver = $user->driver ? $user->driver->id : null;

        //dd($creatorDriver);
        //dd($branchId);

        $branch_prfx = $user->branch ? $user->branch->parcel_tracking_prefix : null;
        $shipment_prefix = $branch_prfx ? $branch_prfx : '';
        $customerId = $user->customer ? $user->customer->id : null;
        $handling_fee = auth()->user()->getBranchHandlingFee();
        //$mpg = auth()->user()->getMPG();
        $total_shipping_cost = $validatedData['total_weight'] * $handling_fee;
       // dd($handling_fee);
        
    	//dd(ConsolidateShipment::generateTrackingNumber());
        $consolidateShipment = ConsolidateShipment::create([
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'customer_id' => $customerId,
            'carrier_id' => $validatedData['carrier_id'] ?? null,
            'driver_id' => $validatedData['driver_id'] ?? null,
            'created_by_driver_id' => $creatorDriver,
            'consolidate_tracking_number' => $shipment_prefix . ConsolidateShipment::generateTrackingNumber() ?? null,
            'consolidation_type' => $validatedData['consolidation_type'] ?? null,
            'consolidated_for' => $validatedData['consolidated_for'] ?? null,
            'customer_email' => $validatedData['customer_email'] ?? null,
            'customer_phone' => $validatedData['customer_phone'] ?? null,
            'receiver_name' => $validatedData['receiver_name'] ?? null,
            'receiver_address' => $validatedData['receiver_address'] ?? null,
            'receiver_email' => $validatedData['receiver_email'] ?? null,
            'receiver_phone' => $validatedData['receiver_phone'] ?? null,
            'origin_warehouse' => $validatedData['origin_warehouse'] ?? null,
            'destination_warehouse' => $validatedData['destination_warehouse'] ?? null,
            'expected_departure_date' => $validatedData['expected_departure_date'] ?? null,
            'expected_arrival_date' => $validatedData['expected_arrival_date'] ?? null,
            'total_weight' => $validatedData['total_weight'] ?? null,
            'total_shipping_cost' => $total_shipping_cost,
            'payment_status' => $validatedData['payment_status'] ?? null,
            'payment_method' => $validatedData['payment_method'] ?? null,
        ]);

    
          if (!empty($validatedData['charge_type']) && is_array($validatedData['charge_type'])) {
                $total = 0;
                $totalDiscount = 0;

                // Process each charge
                foreach ($validatedData['charge_type'] as $i => $chargeType) {
                    $amount = (float)($validatedData['amount'][$i] ?? 0);
                    $discount = (float)($validatedData['discount'][$i] ?? 0);
                    
                    // Calculate totals
                    $total += $amount;
                    $totalDiscount += $discount;
                    $net_total = $total - $totalDiscount;
            
                    // Create charge record
                    ConsolidateShipmentCharges::create([
                        'consolidate_shipment_id' => $consolidateShipment->id,
                        'branch_id' => $branchId ?? null,
                        'charge_type' => $chargeType,
                        'comment' => $validatedData['comment'][$i] ?? null,
                        'units' => $validatedData['units'][$i] ?? null,
                        'rate' => $validatedData['rate'][$i] ?? null,
                        'amount' => $amount,
                        'discount' => $discount,
                        'internal_notes' => $validatedData['internal_notes'][$i] ?? null,
                        'total' => $total,
                        'total_discount' => $totalDiscount,
                        'net_total' => $total - $totalDiscount
                    ]);
                }
            
                // Update shipment with calculated totals
                    $consolidateShipment->update([
                    'consolidate_total_charges' => $total,
                    'consolidate_net_total_charges' => $net_total,
                    'consolidate_total_discount_charges' => $totalDiscount
                ]);
            }

       // dd($consolidateShipment);

        // if($request->hasFile('proof_of_delivery_path')){
        //     $file = $request->file('proof_of_delivery_path');
        //     $uploadedFile = Cloudinary::upload($file->getRealPath(), [
        //         'folder' => 'consolidate_shipment'
        //     ]);

        //     $consolidateShipment->consolidateShipmentDocs()->create([
        //         'proof_of_delivery_path' => $uploadedFile->getSecurePath(),
        //         //'public_id' => $uploadedFile->getPublicId()
        //     ]);
        // }

        if($request->hasFile('proof_of_delivery_path')) {
            $uploadedFile = Cloudinary::upload($request->file('proof_of_delivery_path')->getRealPath(), [
                'folder' => 'Smile_logistics/consolidate_shipment'
            ]);
            
            ConsolidateShipmentDoc::create([
                'consolidate_shipment_id' => $consolidateShipment->id,
                'proof_of_delivery_path' => $uploadedFile->getSecurePath(),
                'public_id' => $uploadedFile->getPublicId()
            ]);
        }

        if($request->hasFile('invoice_path')){
            $uploadedFile = Cloudinary::upload($request->file('invoice_path')->getRealPath(), [
                'folder' => 'Smile_logistics/consolidate_shipment'
            ]);
            
            ConsolidateShipmentDoc::create([
                'consolidate_shipment_id' => $consolidateShipment->id,
                'invoice_path' => $uploadedFile->getSecurePath(),
                'public_id' => $uploadedFile->getPublicId()
            ]);
        }

        if ($request->hasFile('file_path')) {
            //dd($request->file('file_path'));
            $files = $request->file('file_path');
        
            // Normalize to array (even if it's one file)
            $files = is_array($files) ? $files : [$files];
        
            foreach ($files as $file) {
                if ($file->isValid()) {
                    $uploadedFile = Cloudinary::upload($file->getRealPath(), [
                        'folder' => 'Smile_logistics/consolidate_shipment'
                    ]);
        
                    ConsolidateShipmentDoc::create([
                        'consolidate_shipment_id' => $consolidateShipment->id,
                        'file_path' => $uploadedFile->getSecurePath(),
                        'public_id' => $uploadedFile->getPublicId()
                    ]);
                }
            }
        }
    //dd($consolidateShipment->receiver_email);
        Mail::to($consolidateShipment->customer_email)->send(new ConsolidateShipmentCustomerMail($consolidateShipment, $branch));
        Mail::to($consolidateShipment->receiver_email)->send(new ConsolidateShipmentRecieverMail($consolidateShipment, $branch));

        return response()->json([
            'success' => true,
            'message' => 'Consolidate Shipment created successfully',
            'data' => $consolidateShipment
        ]);
    }

    public function update(Request $request, $id)
{
    $user = auth()->user();
    $branchId = $user->getBranchId();

    try {
        $validator = Validator::make($request->all(), [
            'consolidation_type' => 'sometimes|string|nullable',
            'consolidated_for' => 'sometimes|string|nullable',
            'total_weight' => 'sometimes|numeric|nullable',
            'receiver_phone' => 'sometimes|string|nullable',
            'receiver_email' => 'sometimes|email|nullable',
            'origin_warehouse' => 'sometimes|string|nullable',
            'destination_warehouse' => 'sometimes|string|nullable',
            'expected_departure_date' => 'sometimes|date|nullable',
            'expected_arrival_date' => 'sometimes|date|nullable',
            'total_shipping_cost' => 'sometimes|numeric|nullable',
            'payment_status' => 'sometimes|string|nullable',
            'payment_method' => 'sometimes|string|nullable',

            'charge_type' => 'sometimes|array',
            'charge_type.*' => 'sometimes|string',
            'comment' => 'sometimes|array',
            'comment.*' => 'sometimes|string',
            'units' => 'sometimes|array',
            'units.*' => 'sometimes|numeric',
            'rate' => 'sometimes|array',
            'rate.*' => 'sometimes|numeric',
            'amount' => 'sometimes|array',
            'amount.*' => 'sometimes|numeric',
            'discount' => 'sometimes|array',
            'discount.*' => 'sometimes|numeric',
            'internal_notes' => 'sometimes|array',
            'internal_notes.*' => 'sometimes|string',
            'billed' => 'sometimes|array',
            'billed.*' => 'sometimes|boolean',

            'proof_of_delivery_path' => 'sometimes|nullable|file|mimes:pdf,jpg,png|max:2048',
            'invoice_path' => 'sometimes|nullable|file|mimes:pdf,jpg,png|max:2048',
            'file_path' => 'sometimes|array',
            'file_path.*' => 'nullable|file|mimes:pdf,jpg,png,jpeg,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        $validatedShipment = $validator->validated();
        //dd($request->charge_type);
        $consolidateShipment = ConsolidateShipment::findOrFail($id);

        // Update basic shipment info
        $consolidateShipment->update([
            'consolidation_type' => $validatedShipment['consolidation_type'] ?? null,
            'consolidated_for' => $validatedShipment['consolidated_for'] ?? null,
            'total_weight' => $validatedShipment['total_weight'] ?? null,
            'receiver_phone' => $validatedShipment['receiver_phone'] ?? null,
            'receiver_email' => $validatedShipment['receiver_email'] ?? null,
            'origin_warehouse' => $validatedShipment['origin_warehouse'] ?? null,
            'destination_warehouse' => $validatedShipment['destination_warehouse'] ?? null,
            'expected_departure_date' => $validatedShipment['expected_departure_date'] ?? null,
            'expected_arrival_date' => $validatedShipment['expected_arrival_date'] ?? null,
            'total_shipping_cost' => $validatedShipment['total_shipping_cost'] ?? null,
            'payment_status' => $validatedShipment['payment_status'] ?? null,
            'payment_method' => $validatedShipment['payment_method'] ?? null,
        ]);

        // Process charges if they exist
        if (isset($validatedShipment['charge_type'])) {
            $this->processCharges($consolidateShipment, $validatedShipment, $branchId);
        }

        // Handle file uploads
        $this->handleFileUploads($request, $consolidateShipment);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Consolidate Shipment updated successfully',
            'data' => $consolidateShipment
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Update failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Update failed: ' . $e->getMessage()
        ], 500);
    }
}

protected function processCharges($consolidateShipment, $validatedShipment, $branchId)
{
    // Delete existing charges first
    ConsolidateShipmentCharges::where('consolidate_shipment_id', $consolidateShipment->id)->delete();

    $total = 0;
    $totalDiscount = 0;
    
    // Process each charge
    foreach ($validatedShipment['charge_type'] as $index => $chargeType) {
        $amount = (float)($validatedShipment['amount'][$index] ?? 0);
        $discount = (float)($validatedShipment['discount'][$index] ?? 0);
        
        $total += $amount;
        $totalDiscount += $discount;

        ConsolidateShipmentCharges::create([
            'consolidate_shipment_id' => $consolidateShipment->id,
            'branch_id' => $branchId,
            'charge_type' => $chargeType,
            'comment' => $validatedShipment['comment'][$index] ?? null,
            'units' => $validatedShipment['units'][$index] ?? null,
            'rate' => $validatedShipment['rate'][$index] ?? null,
            'amount' => $amount,
            'discount' => $discount,
            'internal_notes' => $validatedShipment['internal_notes'][$index] ?? null,
            'billed' => $validatedShipment['billed'][$index] ?? false,
        ]);
    }

    // Update shipment totals
    $consolidateShipment->update([
        'consolidate_net_total_charges' => $total - $totalDiscount,
        'consolidate_total_discount_charges' => $totalDiscount,
        'consolidate_total_charges' => $total
    ]);
}

protected function handleFileUploads($request, $consolidateShipment)
{
    // Handle proof of delivery
    if ($request->hasFile('proof_of_delivery_path')) {
        $uploadedFile = Cloudinary::upload($request->file('proof_of_delivery_path')->getRealPath(), [
            'folder' => 'Smile_logistics/consolidate_shipment'
        ]);
        
        $consolidateShipment->documents()->updateOrCreate(
            ['consolidate_shipment_id' => $consolidateShipment->id],
            [
                'proof_of_delivery_path' => $uploadedFile->getSecurePath(),
                'public_id' => $uploadedFile->getPublicId()
            ]
        );
    }

    // Handle invoice
    if ($request->hasFile('invoice_path')) {
        $uploadedFile = Cloudinary::upload($request->file('invoice_path')->getRealPath(), [
            'folder' => 'Smile_logistics/consolidate_shipment'
        ]);
        
        $consolidateShipment->documents()->updateOrCreate(
            ['consolidate_shipment_id' => $consolidateShipment->id],
            [
                'invoice_path' => $uploadedFile->getSecurePath(),
                'public_id' => $uploadedFile->getPublicId()
            ]
        );
    }

    // Handle additional files
    if ($request->hasFile('file_path')) {
        foreach ($request->file('file_path') as $file) {
            if ($file->isValid()) {
                $uploadedFile = Cloudinary::upload($file->getRealPath(), [
                    'folder' => 'Smile_logistics/consolidate_shipment'
                ]);
                
                $consolidateShipment->documents()->create([
                    'file_path' => $uploadedFile->getSecurePath(),
                    'public_id' => $uploadedFile->getPublicId()
                ]);
            }
        }
    }
}

    public function getPendingConslidatedDelivery()
    {
        $user = auth()->user();
        $branchId = auth()->user()->getBranchId();
        $consolidateShipment = ConsolidateShipment::with('driver')->where('accepted_status', 'pending')->first();

        return response()->json([
            'success' => true,
            'message' => 'Consolidate Shipment fetched successfully',
            'data' => $consolidateShipment
        ]);
    }

    public function getAcceptedConslidatedDelivery()
    {
        $user = auth()->user();
        $consolidateShipment = ConsolidateShipment::with('driver')->where('accepted_status', 'accepted')
        ->where('branch_id', $branchId)
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Consolidate Shipment fetched successfully',
            'data' => $consolidateShipment
        ]);
    }

    public function acceptConsolidatedDelivery($id)
    {
        $driver = auth()->user();
        $consolidateShipment = ConsolidateShipment::with(['user'])->where('id', $id)->first();
        $consolidateShipment->update(['accepted_status' => 'accepted']);
    
        // Get the user who created the shipment
        if ($consolidateShipment->user_id) {
            $userToNotify = User::find($consolidateShipment->user_id);
            $userToNotify->notify(new DriverAcceptConsolidationDeliveryNotification($consolidateShipment, $driver));
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Consolidate Shipment accepted successfully',
            'data' => $consolidateShipment
        ]);
    }


    public function getPayments()
    {
        $user = auth()->user();
        $payments = ConsolidateShipment::with('branch','user')->where('payment_status', 'paid')->get();

        return response()->json($payments);
    }

    public function showPayment($id)
    {
        $payment = ConsolidateShipment::with('branch','user')->where('id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Consolidate Shipment fetched successfully',
            'data' => $payment
        ]);
    }

    
    public function destroy($id)
    {
        $consolidateShipment = ConsolidateShipment::findOrFail($id);
        $consolidateShipment->delete();
        return response()->json([
            'success' => true,
            'message' => 'Consolidate Shipment deleted successfully',
            'data' => $consolidateShipment
        ]);
    }
}
