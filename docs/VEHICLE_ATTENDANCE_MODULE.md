# Vehicle Attendance Module

## Overview
The Vehicle Attendance Module tracks daily trips for **Hired Bus** and **BRTC Hired Bus** vehicles, with automated monthly billing calculations based on trip completion and distance traveled.

## Features

### 1. **Attendance Recording**
- Track In and Out trips for hired buses
- Record start and end stoppages
- Conditional fields based on trip type and vehicle subtype
- Duplicate prevention (one trip type per vehicle per day)
- AJAX validation and submission

### 2. **Monthly Billing System**
Two different billing methods based on vehicle subtype:

#### **Hired Bus**
- **Billing Method**: Fixed daily rate for 2 trips (In + Out)
- **Calculation**: Completed Days × Daily Rate
- **Completed Day**: When both In and Out trips are recorded for the same date
- **Example**: If daily rate is ৳5,000 and 22 days completed → ৳110,000

#### **BRTC Hired Bus**
- **Billing Method**: Distance-based monthly billing
- **Calculation**: Total Distance (KM) × Price per KM
- **Distance Tracking**: Required for each trip
- **Example**: If 1,500 KM traveled and ৳50/KM → ৳75,000

### 3. **AJAX Features**
- Real-time filtering without page reload
- Search across vehicles, stoppages, remarks
- Filter by vehicle, trip type, date range
- Instant table updates with spinner
- SweetAlert delete confirmation

## Database Structure

### Table: `vehicle_attendances`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| vehicle_id | bigint | Foreign key to vehicles |
| start_stoppage_id | bigint | Foreign key to stoppages |
| end_stoppage_id | bigint | Foreign key to stoppages |
| trip_type | enum('in','out') | Type of trip |
| in_time | time (nullable) | Time for In trip |
| out_time | time (nullable) | Time for Out trip |
| attendance_date | date | Date of attendance |
| total_distance | decimal(8,2) (nullable) | Distance for BRTC buses |
| remarks | text (nullable) | Additional notes |
| user_id | bigint | Who recorded the attendance |
| created_at | timestamp | Record creation time |
| updated_at | timestamp | Record update time |

**Constraints:**
- Unique: `(vehicle_id, attendance_date, trip_type)` - Prevents duplicate trips
- Foreign keys with cascade/restrict policies
- Indexes on vehicle_id, attendance_date, trip_type

## Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | /vehicle-attendances | vehicle-attendances.index | List all attendance |
| GET | /vehicle-attendances/create | vehicle-attendances.create | Create form |
| POST | /vehicle-attendances | vehicle-attendances.store | Store attendance |
| GET | /vehicle-attendances/{id}/edit | vehicle-attendances.edit | Edit form |
| PUT | /vehicle-attendances/{id} | vehicle-attendances.update | Update attendance |
| DELETE | /vehicle-attendances/{id} | vehicle-attendances.destroy | Delete attendance |
| POST | /vehicle-attendances/validate | vehicle-attendances.validate | AJAX validation |
| GET | /vehicle-attendances/monthly-billing | vehicle-attendances.monthly-billing | Monthly billing report |
| GET | /vehicle-attendances/get-vehicle-subtype | vehicle-attendances.get-vehicle-subtype | Get vehicle subtype |

## Views

### 1. **Index View** (`index.blade.php`)
- Displays all attendance records in a table
- AJAX filters: Search, Vehicle, Trip Type, Date Range
- Real-time table updates with spinner
- Pagination without page reload
- SweetAlert delete confirmation
- Shows "Day Complete" status when both trips recorded

**Filters:**
- Search (vehicle, stoppages, remarks)
- Vehicle dropdown
- Trip type (In/Out)
- Date range (from/to)
- Clear all filters button

### 2. **Create View** (`create.blade.php`)
**Conditional Fields:**
- **In Time**: Shows only when Trip Type = "In"
- **Out Time**: Shows only when Trip Type = "Out"
- **Total Distance**: Shows only for BRTC Hired Bus vehicles

**Features:**
- jQuery client-side validation
- Dynamic field visibility based on selections
- Vehicle subtype badge display
- AJAX form submission with spinner
- Real-time validation feedback

**Validation Rules:**
- Vehicle: Required
- Date: Required
- Trip Type: Required (In/Out)
- Start Stoppage: Required
- End Stoppage: Required, must be different from start
- In Time: Required when trip type = "In"
- Out Time: Required when trip type = "Out"
- Total Distance: Required for BRTC Hired Bus, must be > 0

### 3. **Edit View** (`edit.blade.php`)
Same features as create view with pre-filled data.

### 4. **Monthly Billing View** (`monthly-billing.blade.php`)
**Features:**
- Month/Year selector
- Vehicle-by-vehicle breakdown
- Trip details table for each vehicle
- Billing calculation display
- Grand total for all vehicles
- Print functionality
- Color-coded badges for vehicle subtypes

**Displays:**
- **For Hired Bus**: Total trips, Completed days, Daily rate, Total bill
- **For BRTC Hired Bus**: Total trips, Total distance, Price per KM, Total bill
- Individual trip details with date, time, route
- Grand total across all vehicles

## Model Methods

### VehicleAttendance Model

**Relationships:**
```php
vehicle() // BelongsTo Vehicle
startStoppage() // BelongsTo Stoppage
endStoppage() // BelongsTo Stoppage
user() // BelongsTo User
```

**Scopes:**
```php
forVehicle($vehicleId) // Filter by vehicle
dateRange($startDate, $endDate) // Filter by date range
tripType($type) // Filter by trip type
forMonth($year, $month) // Filter by month
```

**Static Methods:**
```php
isDayComplete($vehicleId, $date) // Check if both In & Out trips recorded
getMonthlyBill($vehicleId, $year, $month) // Calculate monthly bill
```

## Controller Methods

### VehicleAttendanceController

**CRUD Methods:**
- `index()` - List with AJAX support
- `create()` - Show create form (only hired bus vehicles)
- `store()` - Save with validation and duplicate check
- `show()` - View single record
- `edit()` - Show edit form
- `update()` - Update with validation
- `destroy()` - Delete record

**Special Methods:**
- `validateAttendance()` - AJAX validation endpoint
- `monthlyBilling()` - Generate monthly billing report
- `getVehicleSubType()` - AJAX endpoint for vehicle subtype

## Usage Flow

### Recording Attendance
1. Navigate to Vehicle Attendance → Record Attendance
2. Select vehicle (only Hired Bus or BRTC Hired Bus shown)
3. Select date and trip type
4. Select start and end stoppages
5. Enter time based on trip type (In Time or Out Time)
6. If BRTC Hired Bus, enter total distance
7. Add remarks (optional)
8. Submit form (validated via jQuery)
9. AJAX saves data with spinner feedback
10. Redirects to index on success

### Viewing Monthly Billing
1. Navigate to Vehicle Attendance → Monthly Billing
2. Select month and year
3. View breakdown for each vehicle:
   - Hired Bus: Shows completed days × daily rate
   - BRTC Hired Bus: Shows total distance × price per km
4. See trip-by-trip details
5. View grand total
6. Print report if needed

## Business Logic

### Hired Bus Billing
```
Monthly Bill = Number of Completed Days × Daily Rate

Completed Day = When both In AND Out trips recorded for same date
```

### BRTC Hired Bus Billing
```
Monthly Bill = Total Distance (KM) × Price per KM

Total Distance = Sum of all trip distances for the month
```

### Day Completion Status
A day is marked as **"Complete"** when:
- Both In trip AND Out trip are recorded
- For the same vehicle
- On the same date

## Validation

### Client-Side (jQuery)
- All required fields checked before submission
- Conditional validation based on trip type and vehicle subtype
- Distance required only for BRTC Hired Bus
- Time required based on trip type
- Start and end stoppages must be different

### Server-Side (Laravel)
- All fields validated on backend
- Duplicate trip prevention
- Foreign key existence checks
- Conditional validation for distance field
- File upload validation (if applicable)

## Technical Features

### AJAX Implementation
✅ Form submission without page reload
✅ Real-time filtering
✅ Spinner during operations
✅ Form field disabling while saving
✅ Validation before submission
✅ Success/error feedback

### Conditional Fields
✅ In Time shown only for In trips
✅ Out Time shown only for Out trips
✅ Distance field shown only for BRTC Hired Bus
✅ Dynamic badge showing vehicle type
✅ Automatic field requirement updates

### Security
✅ CSRF protection
✅ Backend validation
✅ Duplicate prevention
✅ Foreign key constraints
✅ User tracking (who recorded)

## Integration Points

### Required Data
- **Vehicles**: Must have vehicleSubType set to "Hired Bus" or "BRTC Hired Bus"
- **Vehicle Price**: Stored in vehicles.price field
- **Stoppages**: Must have stoppage records
- **Users**: Authenticated user required

### Relationships
- Links to vehicles table
- Links to stoppages table
- Links to users table
- Links to vehicle_sub_types table (via vehicles)

## Reporting

### Monthly Billing Report Features
- Vehicle-wise billing breakdown
- Trip-by-trip details
- Automatic calculation based on vehicle subtype
- Visual separation between vehicle types
- Print-friendly layout
- Grand total summary

## Future Enhancements (Optional)
- Export to Excel/PDF
- SMS/Email notifications for missed trips
- Dashboard widget showing today's incomplete trips
- Bulk attendance entry
- GPS integration for automatic distance calculation
- Driver assignment tracking
- Fuel consumption correlation

## Access Control
To add permissions (optional):
- `vehicle-attendance-view`
- `vehicle-attendance-create`
- `vehicle-attendance-edit`
- `vehicle-attendance-delete`
- `vehicle-attendance-billing`

## Testing Checklist
- [ ] Create In trip for Hired Bus
- [ ] Create Out trip for same bus/date
- [ ] Verify "Day Complete" status shows
- [ ] Create BRTC Hired Bus trip with distance
- [ ] Try creating duplicate trip (should fail)
- [ ] Generate monthly billing for Hired Bus
- [ ] Generate monthly billing for BRTC Hired Bus
- [ ] Verify calculations are correct
- [ ] Test AJAX filters
- [ ] Test delete with SweetAlert
- [ ] Test form validation (all fields)
- [ ] Print billing report

## Installation Complete! 🎉

The Vehicle Attendance Module is now fully functional and ready to use.

**Access URLs:**
- Attendance List: `/vehicle-attendances`
- Record Attendance: `/vehicle-attendances/create`
- Monthly Billing: `/vehicle-attendances/monthly-billing`

