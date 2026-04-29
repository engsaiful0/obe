<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\FeeHead;
use App\Models\PaymentMethod;
use App\Models\FeeCollect;

class FeeCollectTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_pay_for_the_same_fee_head_multiple_times()
    {
        // 1. Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();
        $semester = Semester::factory()->create();
        $student = Student::factory()->create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
        ]);
        $feeHead = FeeHead::factory()->create([
            'semester_id' => $semester->id,
        ]);
        $paymentMethod = PaymentMethod::factory()->create();

        $data = [
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'student_id' => $student->id,
            'payment_method_id' => $paymentMethod->id,
            'fee_heads' => [$feeHead->id],
            'date' => now()->format('Y-m-d'),
        ];

        // 2. Act - First time
        $response = $this->postJson('/collect-fee', $data);

        // 3. Assert - First time
        $response->assertStatus(200);
        $response->assertJson(['success' => 'Fee collected successfully.']);
        $this->assertDatabaseHas('fee_collects', [
            'student_id' => $student->id,
        ]);


        // 4. Act - Second time
        $response = $this->postJson('/collect-fee', $data);


        // 5. Assert - Second time
        $response->assertStatus(422);
        $response->assertJson(['error' => 'This student has already paid for ' . $feeHead->name . '.']);
    }

    public function test_fee_collection_can_be_filtered_by_date_range()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        FeeCollect::factory()->create(['date' => '2023-01-15']);
        FeeCollect::factory()->create(['date' => '2023-01-20']);
        FeeCollect::factory()->create(['date' => '2023-02-01']);

        $response = $this->get('/app/view-collect-fee?from_date=2023-01-01&to_date=2023-01-31');
        $response->assertStatus(200);
        $this->assertCount(2, $response->viewData('feeCollections'));

        $response = $this->get('/app/view-collect-fee?from_date=2023-01-16');
        $response->assertStatus(200);
        $this->assertCount(2, $response->viewData('feeCollections'));

        $response = $this->get('/app/view-collect-fee?to_date=2023-01-16');
        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('feeCollections'));
    }

    public function test_fee_collection_can_be_updated()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $feeCollect = FeeCollect::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();

        $updateData = [
            'academic_year_id' => $feeCollect->academic_year_id,
            'semester_id' => $feeCollect->semester_id,
            'student_id' => $feeCollect->student_id,
            'payment_method_id' => $paymentMethod->id,
            'fee_heads' => json_decode($feeCollect->fee_heads),
            'discount' => 10,
            'date' => now()->format('Y-m-d'),
        ];

        $response = $this->put('/app/collect-fee/' . $feeCollect->id, $updateData);

        $response->assertRedirect('/app/view-collect-fee');
        $this->assertDatabaseHas('fee_collects', ['id' => $feeCollect->id, 'discount' => 10]);
    }

    public function test_fee_collection_can_be_deleted()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $feeCollect = FeeCollect::factory()->create();

        $response = $this->delete('/app/collect-fee/' . $feeCollect->id);

        $response->assertRedirect('/app/view-collect-fee');
        $this->assertDatabaseMissing('fee_collects', ['id' => $feeCollect->id]);
    }
}
