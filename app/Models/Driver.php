<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Driver extends Model
{
    use Notifiable;
    protected $table = 'drivers';
    protected $guarded = [];

    // protected $fillable = [
    //     'user_id',
    //     'branch_id',
    //     'driver_number',
    //     'driver_phone',
    //     'driver_phone_carrier',
    //     'driver_primary_address',
    //     'driver_secondary_address',
    //     'driver_country',
    //     'driver_state',
    //     'driver_city',
    //     'driver_zip',
    //     'office',
    //     'driver_type',
    //     'isAccessToMobileApp',
    //     'mobile_settings',
    //     'emergency_contact_info',
    //     'hired_on',
    //     'terminated_on',
    //     'years_of_experience',
    //     'tags',
    //     'endorsements',
    //     'rating',
    //     'notes_about_the_choices_made',
    //     'pay_via',
    //     'company_name_paid_to',
    //     'employer_identification_number',
    //     'send_settlements_mail',
    //     'print_settlements_under_this_company',
    //     'flash_notes_to_dispatch',
    //     'flash_notes_to_payroll',
    //     'internal_notes',
    //     'driver_status',
    // ];
    
    protected $casts = [
        'tags' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }  

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    
    public function driverDocs()
    {
        return $this->hasMany(DriverDocs::class);
    }
    public function billTo()    
    {
        return $this->hasMany(BillTo::class);
    }
    public function consolidateShipment()
    {
        return $this->hasMany(ConsolidateShipment::class);
    }

    public function shipment()
    {
        return $this->hasMany(Shipment::class);
    }
    public function truck()
    {
        return $this->hasMany(Truck::class);
    }

    public function delivery()
    {
        return $this->hasMany(Delivery::class);
    }

    public function providers()
    {
        return $this->hasMany(AppIntegration::class);
    }
    public function settlement()
    {
        return $this->hasMany(Settlement::class);
    }

    public function hasFeatureAccess(string $featureSlug): bool
    {
        return $this->branch->hasFeatureAccess($featureSlug);
    }
}
