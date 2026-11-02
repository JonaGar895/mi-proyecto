<?php

namespace Database\Seeders;

use App\Models\Inquiry;
use App\Models\Painting;
use App\Models\User;
use Illuminate\Database\Seeder;

class InquirySeeder extends Seeder
{
    public function run(): void
    {
        $buyers = UserSeeder::demoUsers()
            ->reject(fn (User $user) => $user->isAdmin())
            ->values();

        if ($buyers->count() < 4) {
            return;
        }

        $admin = User::query()
            ->where('id', 1)
            ->where('is_admin', true)
            ->first()
            ?? User::query()->where('is_admin', true)->orderBy('id')->first();

        $paintings = Painting::query()
            ->where('status', 'available')
            ->orderBy('id')
            ->take(5)
            ->get();

        if ($paintings->isEmpty()) {
            return;
        }

        $scenarios = [
            [
                'status' => 'pending',
                'message' => 'Me interesa esta obra. ¿Podría darme más detalles sobre el estado de conservación?',
            ],
            [
                'status' => 'contacted',
                'message' => 'He visto la obra en la galería y me gustaría concretar una visita.',
            ],
            [
                'status' => 'rejected',
                'message' => '¿Sigue disponible? Me gustaría hacer una oferta.',
            ],
            [
                'status' => 'closed',
                'message' => 'Consulta sobre disponibilidad y envío.',
            ],
            [
                'status' => 'pending',
                'message' => 'Quisiera saber si el precio admite alguna forma de pago fraccionado.',
            ],
        ];

        foreach ($scenarios as $index => $scenario) {
            $painting = $paintings->get($index % $paintings->count());
            $buyer = $buyers->get($index % $buyers->count());

            if (! $painting || ! $buyer || $buyer->id === $painting->user_id) {
                continue;
            }

            $attributes = [
                'seller_id' => $painting->user_id,
                'message' => $scenario['message'],
                'status' => $scenario['status'],
                'approved_by' => null,
                'approved_at' => null,
                'rejected_by' => null,
                'rejected_at' => null,
            ];

            if ($scenario['status'] === 'rejected' && $admin) {
                $attributes['rejected_by'] = $admin->id;
                $attributes['rejected_at'] = now();
            }

            Inquiry::firstOrCreate(
                [
                    'painting_id' => $painting->id,
                    'buyer_id' => $buyer->id,
                ],
                $attributes,
            );
        }
    }
}
