<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryRequest;
use App\Models\DeliveryRequestLineItem;

class DeliveryRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample delivery request data
        $deliveryRequest = DeliveryRequest::create([
            'id' => 365,
            'mtm' => 'MTM2025061600898',
            'booking_date' => '2025-06-17',
            'delivery_date' => '2025-06-17',
            'delivery_type' => 'Regular',
            'delivery_rate' => 3400.00,
            'company_id' => '2',
            'project_name' => '56A0JNM/Philippines Smart LTE Project 2023',
            'region_id' => '19',
            'status' => '1',
            'customer_id' => '1',
            'truck_type_id' => '2',
            'area_id' => '1',
            'expense_type_id' => '1',
            'delivery_request_type' => '1',
            'created_by' => '1',
            'created_at' => '2025-06-17 08:43:44',
            'updated_at' => '2025-07-09 07:09:00',
        ]);

        // Create a corresponding line item
        DeliveryRequestLineItem::create([
            'mtm' => 'MTM2025061600898',
            'warehouse_id' => ['1'], // Sample warehouse
            'site_name' => ['Sample Site'],
            'delivery_number' => ['DN001'],
            'truck_id' => '16',
            'status' => 'completed',
            'delivery_status' => 'delivered',
            'delivery_address' => ['Sample Address'],
            'distance_type' => '15',
            'add_on_rate' => [1000.00], // Part of the total
            'accessorial_type' => ['Sample Accessorial'],
            'accessorial_rate' => [2400.00], // Part of the total
            'dr_id' => '365',
            'created_by' => '1',
        ]);
    }
}