<?php

namespace Database\Factories;

use App\Models\AttendanceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceRequest>
 */
class AttendanceRequestFactory extends Factory
{
    protected $model = AttendanceRequest::class;

    public function definition(): array
    {
        $startTime = fake()->time('H:i');

        return [
            'user_id' => User::factory(),
            'approver_id' => User::factory(),
            'type' => fake()->randomElement(AttendanceRequest::TYPES),
            'date' => fake()->date(),
            'start_time' => $startTime,
            'end_time' => fake()->optional()->time('H:i'),
            'reason' => fake()->sentence(),
            'proof_image' => null,
            'status' => AttendanceRequest::STATUS_PENDING,
        ];
    }
}
