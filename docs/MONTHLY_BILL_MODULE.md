# Monthly Bill Module Documentation

## Overview
The Monthly Bill Module is a comprehensive system for generating and managing monthly bills for Hired Bus and BRTC Bus vehicles. It automatically calculates bills based on trip completion, distance traveled, and integrates rewards and punishments into the final billing amount.

## Features

### 1. **Bill Generation**
- **Hired Bus**: Fixed daily rate for completed trips (both In and Out trips required for same date)
- **BRTC Bus**: Distance-based billing (total distance × rate per kilometer)
- **Automatic Calculation**: Bills are generated based on vehicle attendance records
- **Date Range Support**: Generate bills for specific date ranges within a month

### 2. **Reward and Punishment Integration**
- **Rewards**: Automatically added to the base bill amount
- **Punishments**: Automatically deducted from the base bill amount
- **Real-time Calculation**: Rewards and punishments are calculated for the specified period

### 3. **Bill Management**
- **Status Tracking**: Draft → Generated → Approved → Paid
- **Detailed View**: Complete breakdown of trips, rewards, and punishments
- **Filtering**: Filter by vehicle, bus type, status, and date range
- **Export**: Export bills to various formats

### 4. **User Interface**
- **Responsive Design**: Works on all device sizes
- **AJAX Operations**: Real-time bill generation and status updates
- **Interactive Filters**: Dynamic filtering without page reload
- **Summary Dashboard**: Overview of bill statistics

## Database Structure

### Table: `monthly_bills`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| vehicle_id | bigint | Foreign key to vehicles table |
| bill_month | string | Format: YYYY-MM |
| from_date | date | Start date of billing period |
| to_date | date | End date of billing period |
| bus_type | enum | 'hired' or 'brtc' |
| base_amount | decimal(12,2) | Base bill amount before adjustments |
| total_rewards | decimal(12,2) | Total rewards to add |
| total_punishments | decimal(12,2) | Total punishments to deduct |
| final_amount | decimal(12,2) | Final amount after adjustments |
| total_trips | integer | For hired: completed days, for BRTC: total trips |
| total_distance | decimal(10,2) | For BRTC: total distance in KM |
| rate_per_km | decimal(8,2) | For BRTC: rate per kilometer |
| daily_rate | decimal(10,2) | For hired: daily rate |
| remarks | text | Additional notes |
| status | enum | 'draft', 'generated', 'approved', 'paid' |
| user_id | bigint | Who generated the bill |

## Usage

### 1. **Generate Single Bill**
1. Navigate to Reports → Monthly Bill
2. Click "Generate Bill" button
3. Select vehicle, year, and month
4. Optionally specify custom date range
5. Click "Generate Bill"

### 2. **Generate All Bills**
1. Click "Generate All" button
2. Select year and month
3. System will generate bills for all hired and BRTC buses

### 3. **View Bill Details**
1. Click on any bill in the list
2. View complete breakdown including:
   - Trip details
   - Rewards and punishments
   - Calculation summary

### 4. **Update Bill Status**
1. Use the action dropdown on each bill
2. Available status transitions:
   - Generated → Approved
   - Approved → Paid

## Calculation Logic

### Hired Bus
```
Base Amount = Completed Days × Daily Rate
Completed Day = Both In and Out trips recorded for same date
Final Amount = Base Amount + Total Rewards - Total Punishments
```

### BRTC Bus
```
Base Amount = Total Distance × Rate per KM
Final Amount = Base Amount + Total Rewards - Total Punishments
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/app/monthly-bill` | List all monthly bills |
| GET | `/app/monthly-bill/create` | Show bill generation form |
| POST | `/app/monthly-bill/generate` | Generate single bill |
| POST | `/app/monthly-bill/generate-all` | Generate all bills |
| GET | `/app/monthly-bill/{id}` | Show bill details |
| PUT | `/app/monthly-bill/{id}/status` | Update bill status |
| GET | `/app/monthly-bill/summary` | Get billing summary |
| GET | `/app/monthly-bill/export` | Export bills |

## Dependencies

- **Vehicle Attendance Records**: Required for trip data
- **Reward Records**: For reward calculations
- **Punishment Records**: For punishment deductions
- **Vehicle Sub Types**: Must be 'Hired Bus' or 'BRTC Hired Bus'
- **Vehicle Pricing**: fixed_price for hired, rate_per_km for BRTC

## Security

- **Authentication Required**: All endpoints require user authentication
- **CSRF Protection**: All forms include CSRF tokens
- **Input Validation**: All inputs are validated before processing
- **Authorization**: Users can only access their own generated bills

## Performance

- **Database Indexing**: Optimized indexes for fast queries
- **Pagination**: Large datasets are paginated for better performance
- **Caching**: Summary data can be cached for better response times
- **AJAX Loading**: Real-time updates without page reload

## Error Handling

- **Graceful Degradation**: System continues to work even if some data is missing
- **User Feedback**: Clear error messages for all operations
- **Validation**: Comprehensive input validation
- **Rollback**: Database transactions ensure data consistency

## Future Enhancements

- **PDF Export**: Generate PDF reports for bills
- **Email Notifications**: Send bill notifications via email
- **Bulk Operations**: Mass status updates and operations
- **Advanced Reporting**: More detailed analytics and reports
- **Integration**: Connect with accounting systems
