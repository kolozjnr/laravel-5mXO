<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Notifications\NewShipmentCreated;

class Shipment extends Model
{
    use Notifiable;
    protected $table = 'shipments';

    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
    public function shipmentTrack() {
        return $this->hasMany(ShipmentTrack::class, 'shipment_id');
    }
    public function shipmentUploads()
    {
        return $this->hasMany(ShipmentUploads::class, 'shipment_id');
    }
    public function shipmentCharges()
    {
        return $this->hasMany(ShipmentCharge::class, 'shipment_id');
    }
    public function shipmentExpenses()
    {
        return $this->hasMany(ShipmentExpense::class, 'shipment_id');
    }
    public function shipmentNotes()
    {
        return $this->hasMany(ShipmentNote::class, 'shipment_id');
    }
    public function consolidatedShipment()
    {
        return $this->belongsToMany(ConsolidatedShipment::class, 'shipment_consolidations');
    }

    function generateTrackingNumber() {
        do {
            $trackingNumber = Str::upper(Str::random(10));
        } while (DB::table('shipments')->where('shipment_tracking_number', $trackingNumber)->exists()); 
    
        return $trackingNumber;
    }

    protected static function booted()
    {
        static::created(function ($shipment) {
            $user = User::find($shipment->user_id);
            if ($user) {
                $user->notify(new NewShipmentCreated($shipment));
            }
        });
    }
}
