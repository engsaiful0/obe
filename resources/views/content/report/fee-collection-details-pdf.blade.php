<!DOCTYPE html>
<html>
<head>
    <title>Fee Collection Details</title>
    <style>
        body { font-family: sans-serif; }
        .container { width: 100%; margin: 0 auto; }
        .header, .footer { text-align: center; }
        .header h1, .header h2 { margin: 0; }
        .content { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($appSetting && $appSetting->logo)
                <img src="{{ public_path('profile_pictures/'.$appSetting->logo) }}" alt="logo" width="100">
            @endif
            <h1>{{ $appSetting->name ?? 'School Name' }}</h1>
            <h2>Fee Collection Details</h2>
        </div>

        <div class="content">
            <p><strong>Student Name:</strong> {{ $feeCollection->student->full_name_in_english_block_letter ?? '' }}</p>
            <p><strong>Academic Year:</strong> {{ $feeCollection->academic_year->academic_year_name ?? '' }}</p>
            <p><strong>Semester:</strong> {{ $feeCollection->semester->semester_name ?? '' }}</p>
            <p><strong>Date:</strong> {{ $feeCollection->date }}</p>

            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Fee Head</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @if(is_array($feeCollection->fee_heads))
                        @foreach($feeCollection->fee_heads as $head)
                            <tr>
                                <td>{{ $head['fee_head_name'] ?? '' }}</td>
                                <td>{{ $head['amount'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align: right;"><strong>Total Amount:</strong></td>
                        <td>{{ $feeCollection->total_amount }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="footer">
            <p>Thank you!</p>
        </div>
    </div>
</body>
</html>
