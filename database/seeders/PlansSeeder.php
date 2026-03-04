<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Trial',
                'price' => 0.00,
                'max_users' => 10,
                'max_stores' => 1,
                'description' => 'Free trial plan with basic features for 30 days',
                'features' => [
                    'Basic inventory management',
                    'Sales tracking',
                    'Basic reporting',
                    '1 User account',
                    '1 Store location',
                    '30 days trial'
                ],
                'is_active' => true,
                'trial_days' => 30,
            ],
            [
                'name' => 'Starter',
                'price' => 29.99,
                'max_users' => 3,
                'max_stores' => 2,
                'description' => 'Perfect for small businesses getting started',
                'features' => [
                    'Complete inventory management',
                    'Sales & purchase tracking',
                    'Advanced reporting',
                    'Up to 3 users',
                    'Up to 2 store locations',
                    'Email support'
                ],
                'is_active' => true,
                'trial_days' => 14,
            ],
            [
                'name' => 'Professional',
                'price' => 59.99,
                'max_users' => 10,
                'max_stores' => 5,
                'description' => 'Ideal for growing businesses with multiple locations',
                'features' => [
                    'All Starter features',
                    'Multi-location management',
                    'Advanced analytics',
                    'Up to 10 users',
                    'Up to 5 store locations',
                    'Priority support',
                    'Custom reports'
                ],
                'is_active' => true,
                'trial_days' => 14,
            ],
            [
                'name' => 'Enterprise',
                'price' => 119.99,
                'max_users' => 50,
                'max_stores' => 20,
                'description' => 'For large businesses with complex needs',
                'features' => [
                    'All Professional features',
                    'Unlimited products',
                    'Advanced integrations',
                    'Up to 50 users',
                    'Up to 20 store locations',
                    '24/7 phone support',
                    'Custom development',
                    'API access'
                ],
                'is_active' => true,
                'trial_days' => 14,
            ]
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
