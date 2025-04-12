<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Branch;
use Illuminate\Http\Request;
use App\Models\ConsolidateShipment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConsolidateShipmentCustomerMail;
use App\Mail\ConsolidateShipmentRecieverMail;
use App\Http\Requests\StoreConsolidateShipmentRequest;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ConsolidateShipmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch ? $user->branch->id : null;
        $consolidateShipments = ConsolidateShipment::where('branch_id', $branchId)->with('customer.user', 'carrier', 'driver.user')->latest()->get();
        return response()->json(['consolidateShipments' => $consolidateShipments], 200);
    }
    public function show($id)
    {
        $user = auth()->user();
        $branchId = $user->branch ? $user->branch->id : null;
        $consolidateShipment = ConsolidateShipment::where('branch_id', $branchId)->with('customer', 'carrier', 'driver')->findOrFail($id);
        return response()->json(['consolidateShipment' => $consolidateShipment], 200);
    }
    public function store(StoreConsolidateShipmentRequest $request)
    {
       // dd($request->all());
        $validatedData = $request->validated();
        $user = auth()->user();
        $branchId = $user->branch ? $user->branch->id : null;
        $branch = $user->branch()->with('user')->first();

        $branch_prfx = $user->branch ? $user->branch->parcel_tracking_prefix : null;
        $shipment_prefix = $branch_prfx ? $branch_prfx : '';
        $branchId = $user->branch ? $user->branch->id : null;
        $customerId = $user->customer ? $user->customer->id : null;
        
    	//dd(ConsolidateShipment::generateTrackingNumber());
        $consolidateShipment = ConsolidateShipment::create([
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'customer_id' => $customerId,
            //'carrier_id' => $validatedData['carrier_id'],
            'driver_id' => $validatedData['driver_id'],
            'consolidate_tracking_number' => $shipment_prefix . ConsolidateShipment::generateTrackingNumber() ?? null,
            'consolidation_type' => $validatedData['consolidation_type'],
            'consolidated_for' => $validatedData['consolidated_for'],
            'customer_email' => $validatedData['customer_email'],
            'customer_phone' => $validatedData['customer_phone'],
            'receiver_name' => $validatedData['receiver_name'],
            'receiver_address' => $validatedData['receiver_address'],
            'receiver_email' => $validatedData['receiver_email'],
            'receiver_phone' => $validatedData['receiver_phone'],
            'origin_warehouse' => $validatedData['origin_warehouse'],
            'destination_warehouse' => $validatedData['destination_warehouse'],
            'expected_departure_date' => $validatedData['expected_departure_date'],
            'expected_arrival_date' => $validatedData['expected_arrival_date'],
            'total_weight' => $validatedData['total_weight'],
            'total_shipping_cost' => $validatedData['total_shipping_cost'],
            'payment_status' => $validatedData['payment_status'],
            'payment_method' => $validatedData['payment_method'],
        ]);

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
                'folder' => 'consolidate_shipment'
            ]);
            
            $consolidateShipment->documents()->create([
                'type' => 'proof_of_delivery',
                'file_path' => $uploadedFile->getSecurePath()
            ]);
        }

        if($request->hasFile('invoice_path')){
            $uploadedFile = Cloudinary::upload($request->file('invoice_path')->getRealPath(), [
                'folder' => 'consolidate_shipment'
            ]);
            
            $consolidateShipment->documents()->create([
                'type' => 'invoice_path',
                'file_path' => $uploadedFile->getSecurePath()
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
                        'folder' => 'consolidate_shipment'
                    ]);
        
                    $consolidateShipment->consolidateShipmentDocs()->create([
                        'file_path' => $uploadedFile->getSecurePath(),
                        //'public_id' => $uploadedFile->getPublicId()
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
    $validated = $request->validate([
        'consolidation_type' => 'sometimes|string',
        'consolidated_for' => 'sometimes|string',
        'total_weight' => 'sometimes|numeric',
        'receiver_phone' => 'sometimes|string',
        'receiver_email' => 'sometimes|email',
        'origin_warehouse' => 'sometimes|string',
        'destination_warehouse' => 'sometimes|string',
        'expected_departure_date' => 'sometimes|date_format:Y-m-d',
        'expected_arrival_date' => 'sometimes|date_format:Y-m-d',
        'total_shipping_cost' => 'sometimes|numeric',
        'payment_status' => 'sometimes|string',
        'payment_method' => 'sometimes|string',

        'proof_of_delivery_path' => 'sometimes|file|mimes:pdf,jpg,png|max:2048',
        'invoice_path' => 'sometimes|file|mimes:pdf,jpg,png|max:2048',
        'file_path' => 'sometimes|file|mimes:pdf,jpg,png,jpeg,doc,docx|max:5120',
    ]);

    $consolidateShipment = ConsolidateShipment::findOrFail($id);

    $fields = [
        'consolidation_type',
        'consolidated_for',
        'total_weight',
        'receiver_phone',
        'receiver_email',
        'origin_warehouse',
        'destination_warehouse',
        'expected_departure_date',
        'expected_arrival_date',
        'total_shipping_cost',
        'payment_status',
        'payment_method'
    ];

    $updates = [];

    foreach ($fields as $field) {
        if ($request->has($field) && $request->$field !== $consolidateShipment->$field) {
            $updates[$field] = $request->$field;
        }
    }

    if (!empty($updates)) {
        $consolidateShipment->update($updates);
    }

    if ($request->hasFile('proof_of_delivery_path')) {
        $uploadedFile = Cloudinary::upload($request->file('proof_of_delivery_path')->getRealPath(), [
            'folder' => 'consolidate_shipment'
        ]);
        $consolidateShipment->documents()->updateOrCreate(
            ['type' => 'proof_of_delivery'],
            ['file_path' => $uploadedFile->getSecurePath()]
        );
    }

    if ($request->hasFile('invoice_path')) {
        $uploadedFile = Cloudinary::upload($request->file('invoice_path')->getRealPath(), [
            'folder' => 'consolidate_shipment'
        ]);
        $consolidateShipment->documents()->updateOrCreate(
            ['type' => 'invoice_path'],
            ['file_path' => $uploadedFile->getSecurePath()]
        );
    }

    if ($request->hasFile('file_path')) {
        $uploadedFile = Cloudinary::upload($request->file('file_path')->getRealPath(), [
            'folder' => 'consolidate_shipment'
        ]);
        $consolidateShipment->documents()->updateOrCreate(
            ['type' => 'file_path'],
            ['file_path' => $uploadedFile->getSecurePath()]
        );
    }

    return response()->json([
        'success' => true,
        'message' => 'Consolidate Shipment updated successfully',
        'data' => $consolidateShipment
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
