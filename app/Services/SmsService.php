<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\FeeCollect;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $appSetting;

    public function __construct()
    {
        $this->appSetting = AppSetting::first();
    }

    /**
     * Check if SMS is enabled in app settings
     */
    public function isSmsEnabled()
    {
        if (!$this->appSetting) {
            Log::warning('App settings not found, SMS functionality disabled');
            return false;
        }

        // Check if sms_status is 'on'
        return $this->appSetting->sms_status === 'on';
    }

    /**
     * Send SMS to a single number
     */
    public function sendSms($number, $message)
    {
        // Check if SMS is enabled before sending
        if (!$this->isSmsEnabled()) {
            Log::info('SMS sending is disabled in app settings. Skipping SMS to: ' . $number);
            return false;
        }

        if (!$this->appSetting || !$this->appSetting->api_key || !$this->appSetting->sender_id) {
            Log::error('SMS configuration not found or incomplete');
            return false;
        }

        $url = $this->appSetting->sms_url ?: "http://bulksmsbd.net/api/smsapi";
        $api_key = $this->appSetting->api_key;
        $senderid = $this->appSetting->sender_id;

        $data = [
            "api_key" => $api_key,
            "senderid" => $senderid,
            "number" => $number,
            "message" => $message
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Log::error('SMS cURL Error: ' . $error);
            return false;
        }

        if ($httpCode !== 200) {
            Log::error('SMS API Error: HTTP ' . $httpCode . ' - ' . $response);
            return false;
        }

        Log::info('SMS sent successfully to ' . $number . ': ' . $response);
        return $response;
    }

    /**
     * Send fee collection SMS to student and guardian
     */
    public function sendFeeCollectionSms(FeeCollect $feeCollect)
    {
        try {
            // Check if SMS is enabled in app settings
            if (!$this->isSmsEnabled()) {
                Log::info('SMS sending is disabled. Skipping fee collection SMS for fee collection ID: ' . $feeCollect->id);
                return true; // Return true as this is not an error, just disabled
            }

            $student = $feeCollect->student;
            if (!$student) {
                Log::error('Student not found for fee collection ID: ' . $feeCollect->id);
                return false;
            }

            // Prepare fee details
            $feeDetails = $this->prepareFeeDetails($feeCollect);
            
            // Send SMS to student
            if ($student->personal_number) {
                $studentMessage = $this->buildStudentMessage($student, $feeDetails);
                $result = $this->sendSms($student->personal_number, $studentMessage);
                if ($result) {
                    Log::info('Fee collection SMS sent to student: ' . $student->personal_number . ' for fee collection ID: ' . $feeCollect->id);
                }
            }

            // Send SMS to guardian
            if ($student->guardian_phone) {
                $guardianMessage = $this->buildGuardianMessage($student, $feeDetails);
                $result = $this->sendSms($student->guardian_phone, $guardianMessage);
                if ($result) {
                    Log::info('Fee collection SMS sent to guardian: ' . $student->guardian_phone . ' for fee collection ID: ' . $feeCollect->id);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending fee collection SMS: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Prepare fee details from fee collection
     */
    protected function prepareFeeDetails(FeeCollect $feeCollect)
    {
        $feeHeads = is_string($feeCollect->fee_heads) ? json_decode($feeCollect->fee_heads, true) : ($feeCollect->fee_heads ?? []);
        $feeHeads = is_array($feeHeads) ? $feeHeads : [];
        $feeDetails = [];

        foreach ($feeHeads as $feeHead) {
            $feeDetails[] = [
                'name' => $feeHead['name'],
                'amount' => $feeHead['amount']
            ];
        }

        return [
            'fee_heads' => $feeDetails,
            'total_amount' => $feeCollect->total_amount,
            'net_payable' => $feeCollect->net_payable,
            'discount' => $feeCollect->discount ?? 0,
            'fine_amount' => $feeCollect->fine_amount ?? 0,
            'date' => $feeCollect->date,
            'academic_year' => $feeCollect->academic_year->name ?? '',
            'semester' => $feeCollect->semester->name ?? '',
            'months' => $feeCollect->months
        ];
    }

    /**
     * Build message for student
     */
    protected function buildStudentMessage(Student $student, array $feeDetails)
    {
        $appName = $this->appSetting->app_name ?? 'Polytechnic Institute';
        $studentName = $student->full_name_in_english_block_letter;
        
        $message = "Dear {$studentName},\n";
        $message .= "Fee payment received successfully!\n\n";
        
        // Add fee details
        foreach ($feeDetails['fee_heads'] as $fee) {
            $message .= "• {$fee['name']}: ৳{$fee['amount']}\n";
        }
        
        if ($feeDetails['fine_amount'] > 0) {
            $message .= "• Fine: ৳{$feeDetails['fine_amount']}\n";
        }
        
        if ($feeDetails['discount'] > 0) {
            $message .= "• Discount: -৳{$feeDetails['discount']}\n";
        }
        
        $message .= "Total Paid: ৳{$feeDetails['net_payable']}\n";
        $message .= "Date: {$feeDetails['date']}\n";
        $message .= "Academic Year: {$feeDetails['academic_year']}\n";
        $message .= "Semester: {$feeDetails['semester']}\n";
        
        if (!empty($feeDetails['months'])) {
            $monthNames = \App\Models\Month::whereIn('id', $feeDetails['months'])->pluck('month_name')->toArray();
            $message .= "Months: " . implode(', ', $monthNames) . "\n";
        }
        
        $message .= "\nThank you!\n{$appName}";
        
        return $message;
    }

    /**
     * Build message for guardian
     */
    protected function buildGuardianMessage(Student $student, array $feeDetails)
    {
        $appName = $this->appSetting->app_name ?? 'Polytechnic Institute';
        $studentName = $student->full_name_in_english_block_letter;
        $guardianName = $student->guardian_name_absence_of_father ?? 'Guardian';
        
        $message = "Dear {$guardianName},\n";
        $message .= "Fee payment received for {$studentName}!\n\n";
        
        // Add fee details
        foreach ($feeDetails['fee_heads'] as $fee) {
            $message .= "• {$fee['name']}: ৳{$fee['amount']}\n";
        }
        
        if ($feeDetails['fine_amount'] > 0) {
            $message .= "• Fine: ৳{$feeDetails['fine_amount']}\n";
        }
        
        if ($feeDetails['discount'] > 0) {
            $message .= "• Discount: -৳{$feeDetails['discount']}\n";
        }
        
        $message .= "Total Paid: ৳{$feeDetails['net_payable']}\n";
        $message .= "Date: {$feeDetails['date']}\n";
        $message .= "Academic Year: {$feeDetails['academic_year']}\n";
        $message .= "Semester: {$feeDetails['semester']}\n";
        
        if (!empty($feeDetails['months'])) {
            $monthNames = \App\Models\Month::whereIn('id', $feeDetails['months'])->pluck('month_name')->toArray();
            $message .= "Months: " . implode(', ', $monthNames) . "\n";
        }
        
        $message .= "\nThank you!\n{$appName}";
        
        return $message;
    }

    /**
     * Send custom SMS
     */
    public function sendCustomSms($numbers, $message)
    {
        // Check if SMS is enabled before sending
        if (!$this->isSmsEnabled()) {
            Log::info('SMS sending is disabled in app settings. Skipping custom SMS.');
            return false;
        }

        if (is_array($numbers)) {
            $numbers = implode(',', $numbers);
        }
        
        return $this->sendSms($numbers, $message);
    }
}
