<?php

use App\Http\Controllers\AssessmentComponentController;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\authentications\ForgotPasswordCover;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\RegisterCover;
use App\Http\Controllers\authentications\RegisterMultiSteps;
use App\Http\Controllers\authentications\ResetPasswordBasic;
use App\Http\Controllers\authentications\ResetPasswordCover;
use App\Http\Controllers\authentications\TwoStepsBasic;
use App\Http\Controllers\authentications\TwoStepsCover;
use App\Http\Controllers\authentications\VerifyEmailBasic;
use App\Http\Controllers\authentications\VerifyEmailCover;
use App\Http\Controllers\BloomController;
use App\Http\Controllers\BRTCBusMonthlyBillController;
use App\Http\Controllers\BusHelperReportController;
use App\Http\Controllers\BusRequisitionController;
use App\Http\Controllers\BusScheduleKeywordController;
use App\Http\Controllers\CacheController;
use App\Http\Controllers\CloController;
use App\Http\Controllers\CloPoMappingController;
use App\Http\Controllers\CourseAssignmentCascadeController;
use App\Http\Controllers\CourseAssignmentController;
use App\Http\Controllers\DailyBusListController;
use App\Http\Controllers\DamageController;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\DaywiseTripReportController;
use App\Http\Controllers\DeploymentPlanController;
use App\Http\Controllers\DriverHelperAssignmentController;
use App\Http\Controllers\DriverHelperAssignmentReportController;
use App\Http\Controllers\DriverTripReportController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FuelController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\IssueReportController;
use App\Http\Controllers\layouts\Vertical;
use App\Http\Controllers\LubricantController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\MonthlyBillController;
use App\Http\Controllers\MonthlySalarySettingController;
use App\Http\Controllers\MyCourseController;
use App\Http\Controllers\PeoController;
use App\Http\Controllers\ProgramOutcomeController;
use App\Http\Controllers\PunishmentReportController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReportController;
use App\Http\Controllers\QuestionCloMappingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RewardReportController;
use App\Http\Controllers\settings\AcademicSession;
use App\Http\Controllers\settings\AppSettings;
use App\Http\Controllers\settings\Batch;
use App\Http\Controllers\settings\BloodGroup;
use App\Http\Controllers\settings\Board;
use App\Http\Controllers\settings\Brand;
use App\Http\Controllers\settings\BusRoute;
use App\Http\Controllers\settings\BusSubType;
use App\Http\Controllers\settings\BusType;
use App\Http\Controllers\settings\BusUser;
use App\Http\Controllers\settings\Color;
use App\Http\Controllers\settings\Course;
use App\Http\Controllers\settings\DeploymentType;
use App\Http\Controllers\settings\Designation;
use App\Http\Controllers\settings\DriverType;
use App\Http\Controllers\settings\EducationalQualification;
use App\Http\Controllers\settings\EmployeeType;
use App\Http\Controllers\settings\ExpenseHead;
use App\Http\Controllers\settings\ExperienceYear;
use App\Http\Controllers\settings\Faculty;
use App\Http\Controllers\settings\FeeHead;
use App\Http\Controllers\settings\FeeSettings;
use App\Http\Controllers\settings\Gender;
use App\Http\Controllers\settings\Grade;
use App\Http\Controllers\settings\IncomeHead;
use App\Http\Controllers\settings\IssuingAuthority;
use App\Http\Controllers\settings\Item;
use App\Http\Controllers\settings\LicenseType;
use App\Http\Controllers\settings\MaritalStatus;
use App\Http\Controllers\settings\Month;
use App\Http\Controllers\settings\Nationality;
use App\Http\Controllers\settings\PaymentMethod;
use App\Http\Controllers\settings\PermissionSetting;
use App\Http\Controllers\settings\Program;
use App\Http\Controllers\settings\PunishmentType;
use App\Http\Controllers\settings\RelatedTo as RelatedToSettingController;
use App\Http\Controllers\settings\Religion;
use App\Http\Controllers\settings\RewardType;
use App\Http\Controllers\settings\Section as SectionSettingsController;
use App\Http\Controllers\settings\Semester;
use App\Http\Controllers\settings\Status;
use App\Http\Controllers\settings\Stoppage;
use App\Http\Controllers\settings\Supplier as SettingsSupplier;
use App\Http\Controllers\settings\Teacher as TeacherSettingsController;
use App\Http\Controllers\settings\TripTimeController;
use App\Http\Controllers\settings\Unit;
use App\Http\Controllers\settings\User;
use App\Http\Controllers\settings\ViolationType;
use App\Http\Controllers\settings\Warehouse as SettingsWarehouse;
use App\Http\Controllers\settings\Year;
use App\Http\Controllers\StockReportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentInReportController;
use App\Http\Controllers\StudentMarkController;
use App\Http\Controllers\StudentOutReportController;
use App\Http\Controllers\Supplier;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\VisionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/app/expense-report', [ReportController::class, 'expenseReport'])->name('expense-report');
Route::post('/app/expense-report/ajax', [ReportController::class, 'expenseReportAjax'])->name('expense-report.ajax');
Route::get('/app/expense-report/print-list', [ReportController::class, 'printExpenseReportList'])->name('expense-report.print-list');
Route::get('/app/expense-report/pdf', [ReportController::class, 'expenseReportPdf'])->name('expense-report.pdf');
Route::get('/app/expense-report/excel', [ReportController::class, 'expenseReportExcel'])->name('expense-report.excel');

// Helper List Routes
use App\Http\Controllers\HelperListController;

Route::get('/app/helper-list', [HelperListController::class, 'index'])->name('helper-list');
Route::post('/app/helper-list/ajax', [HelperListController::class, 'ajax'])->name('helper-list.ajax');
Route::get('/app/helper-list/pdf', [HelperListController::class, 'pdf'])->name('helper-list.pdf');
Route::get('/app/helper-list/excel', [HelperListController::class, 'excel'])->name('helper-list.excel');
Route::get('/app/helper-list/print', [HelperListController::class, 'print'])->name('helper-list.print');

// Driver List Routes
use App\Http\Controllers\DriverListController;

Route::get('/app/driver-list', [DriverListController::class, 'index'])->name('driver-list');
Route::post('/app/driver-list/ajax', [DriverListController::class, 'ajax'])->name('driver-list.ajax');
Route::get('/app/driver-list/pdf', [DriverListController::class, 'pdf'])->name('driver-list.pdf');
Route::get('/app/driver-list/excel', [DriverListController::class, 'excel'])->name('driver-list.excel');
Route::get('/app/driver-list/print', [DriverListController::class, 'print'])->name('driver-list.print');

// Bus Helper Report Routes
Route::get('/app/bus-helper-report', [BusHelperReportController::class, 'index'])->name('bus-helper-report');
Route::post('/app/bus-helper-report/ajax', [BusHelperReportController::class, 'ajax'])->name('bus-helper-report.ajax');
Route::get('/app/bus-helper-report/pdf', [BusHelperReportController::class, 'pdf'])->name('bus-helper-report.pdf');
Route::get('/app/bus-helper-report/excel', [BusHelperReportController::class, 'excel'])->name('bus-helper-report.excel');
Route::get('/app/bus-helper-report/print', [BusHelperReportController::class, 'print'])->name('bus-helper-report.print');

// BRTC Bus Monthly Bill Report Routes
Route::get('/app/brtc-bus-monthly-bill', [BRTCBusMonthlyBillController::class, 'index'])->name('brtc-bus-monthly-bill');
Route::get('/app/brtc-bus-monthly-bill/print-list', [BRTCBusMonthlyBillController::class, 'printList'])->name('brtc-bus-monthly-bill.print-list');
Route::get('/app/brtc-bus-monthly-bill/pdf', [BRTCBusMonthlyBillController::class, 'pdf'])->name('brtc-bus-monthly-bill.pdf');
Route::get('/app/brtc-bus-monthly-bill/excel', [BRTCBusMonthlyBillController::class, 'excel'])->name('brtc-bus-monthly-bill.excel');

// Daywise Trip Report Routes
Route::get('/app/daywise-trip-report', [DaywiseTripReportController::class, 'index'])->name('daywise-trip-report');
Route::get('/app/daywise-trip-report/get-buses-by-sub-type', [DaywiseTripReportController::class, 'getBusesBySubType'])->name('daywise-trip-report.get-buses-by-sub-type');
Route::get('/app/daywise-trip-report/print-list', [DaywiseTripReportController::class, 'printList'])->name('daywise-trip-report.print-list');
Route::get('/app/daywise-trip-report/pdf', [DaywiseTripReportController::class, 'pdf'])->name('daywise-trip-report.pdf');
Route::get('/app/daywise-trip-report/excel', [DaywiseTripReportController::class, 'excel'])->name('daywise-trip-report.excel');

// Student IN Report Routes
Route::get('/app/student-in-report', [StudentInReportController::class, 'index'])->name('student-in-report');
Route::get('/app/student-in-report/print', [StudentInReportController::class, 'print'])->name('student-in-report.print');
Route::get('/app/student-in-report/pdf', [StudentInReportController::class, 'pdf'])->name('student-in-report.pdf');
Route::get('/app/student-in-report/excel', [StudentInReportController::class, 'excel'])->name('student-in-report.excel');

// Student OUT Report Routes
Route::get('/app/student-out-report', [StudentOutReportController::class, 'index'])->name('student-out-report');
Route::get('/app/student-out-report/print', [StudentOutReportController::class, 'print'])->name('student-out-report.print');
Route::get('/app/student-out-report/pdf', [StudentOutReportController::class, 'pdf'])->name('student-out-report.pdf');
Route::get('/app/student-out-report/excel', [StudentOutReportController::class, 'excel'])->name('student-out-report.excel');

// Student registration (create student)
Route::get('/app/student/view-student', [StudentController::class, 'index'])->name('student.view-student');
Route::get('/app/student/list', [StudentController::class, 'listPaginated'])->name('student.list');
Route::get('/app/student/add-student', [StudentController::class, 'create'])->name('student.add-student');
Route::get('/app/student/create-meta', [StudentController::class, 'createMeta'])->name('student.create-meta');
Route::get('/app/student/batches-by-program', [StudentController::class, 'batchesByProgram'])->name('student.batches-by-program');
Route::post('/app/student', [StudentController::class, 'store'])->name('students.store');
Route::get('/app/student/{student}/show', [StudentController::class, 'show'])->name('student.show');
Route::get('/app/student/{student}/edit', [StudentController::class, 'edit'])->name('student.edit');
Route::put('/app/student/{student}', [StudentController::class, 'update'])->name('student.update');
Route::delete('/app/student/{student}', [StudentController::class, 'destroy'])->name('student.destroy');
Route::resource('teachers', TeacherController::class);

Route::prefix('/app/my-courses')->name('my-courses.')->group(function () {
    Route::get('/', [MyCourseController::class, 'courseList'])->name('course-list');
    Route::get('/{courseAssignment}/marks', [MyCourseController::class, 'marksEntry'])->name('marks-entry');
    Route::get('/{courseAssignment}/students', [MyCourseController::class, 'students'])->name('students');
    Route::post('/{courseAssignment}/save-marks', [MyCourseController::class, 'saveMarks'])->name('save-marks');
    Route::get('/{courseAssignment}/template', [MyCourseController::class, 'downloadTemplate'])->name('download-template');
    Route::post('/{courseAssignment}/import', [MyCourseController::class, 'importMarks'])->name('import');
});

Route::prefix('ajax')->group(function () {
    Route::get('program/{program}/batches', [CourseAssignmentCascadeController::class, 'programBatches'])
        ->name('ajax.program.batches');
    Route::get('program/{program}/semesters', [CourseAssignmentCascadeController::class, 'programSemesters'])
        ->name('ajax.program.semesters');
    Route::get('program/{program}/courses', [CourseAssignmentCascadeController::class, 'programCourses'])
        ->name('ajax.program.courses');
    Route::get('program/{program}/courses-for-clo', [CloController::class, 'coursesByProgram'])
        ->name('ajax.clo.program.courses');
    Route::prefix('clo-po')->group(function () {
        Route::get('program/{program}/courses', [CloPoMappingController::class, 'coursesByProgram'])
            ->name('ajax.clo_po.program.courses');
        Route::get('program/{program}/program-outcomes', [CloPoMappingController::class, 'programOutcomesByProgram'])
            ->name('ajax.clo_po.program.program_outcomes');
        Route::get('course/{course}/clos', [CloPoMappingController::class, 'closByCourse'])
            ->name('ajax.clo_po.course.clos');
    });
    Route::prefix('question-clo')->group(function () {
        Route::get('program/{program}/courses', [QuestionCloMappingController::class, 'coursesByProgram'])
            ->name('ajax.question_clo.program.courses');
        Route::get('course/{course}/assessment-components', [QuestionCloMappingController::class, 'assessmentComponentsByCourse'])
            ->name('ajax.question_clo.course.assessment_components');
        Route::get('course/{course}/clos', [QuestionCloMappingController::class, 'closByCourse'])
            ->name('ajax.question_clo.course.clos');
        Route::get('clo/{clo}/bloom', [QuestionCloMappingController::class, 'bloomByClo'])
            ->name('ajax.question_clo.clo.bloom');
    });
    Route::prefix('assessment-components')->group(function () {
        Route::get('program/{program}/courses', [AssessmentComponentController::class, 'coursesByProgram'])
            ->name('ajax.assessment_components.program.courses');
    });
    Route::get('batch/{batch}/sections', [CourseAssignmentCascadeController::class, 'batchSections'])
        ->name('ajax.batch.sections');
    Route::get('semester/{semester}/courses', [CourseAssignmentCascadeController::class, 'semesterCourses'])
        ->name('ajax.semester.courses');
});

Route::resource('course-assignments', CourseAssignmentController::class)->names([
    'index' => 'course-assignment.index',
    'create' => 'course-assignment.create',
    'store' => 'course-assignment.store',
    'show' => 'course-assignment.show',
    'edit' => 'course-assignment.edit',
    'update' => 'course-assignment.update',
    'destroy' => 'course-assignment.destroy',
]);

Route::resource('visions', VisionController::class);
Route::resource('missions', MissionController::class);
Route::resource('peos', PeoController::class);

Route::resource('blooms', BloomController::class);
Route::resource('clos', CloController::class);

Route::get('clo-po-mappings/matrix', [CloPoMappingController::class, 'matrix'])->name('clo-po-mappings.matrix');
Route::resource('clo-po-mappings', CloPoMappingController::class);

Route::resource('assessment-components', AssessmentComponentController::class);

Route::get('question-clo-mappings/matrix', [QuestionCloMappingController::class, 'matrix'])->name('question-clo-mappings.matrix');
Route::resource('question-clo-mappings', QuestionCloMappingController::class);

Route::get('student-marks/bulk', [StudentMarkController::class, 'bulkEntry'])->name('student-marks.bulk');

Route::post('student-marks/bulk-save', [StudentMarkController::class, 'saveBulkMarks'])->name('student-marks.bulk-save');
Route::get('student-marks/view', [StudentMarkController::class, 'view'])->name('student-marks.view');

Route::get('student-marks/template', [StudentMarkController::class, 'downloadTemplate'])->name('student-marks.template');

Route::post('student-marks/import', [StudentMarkController::class, 'importExcel'])->name('student-marks.import');

Route::post('student-marks/reset', [StudentMarkController::class, 'resetMarks'])->name('student-marks.reset');

Route::get('student-marks/api/students', [StudentMarkController::class, 'getStudentsByFilter'])->name('student-marks.api.students');
Route::get('student-marks/api/questions', [StudentMarkController::class, 'getQuestionsByComponent'])->name('student-marks.api.questions');

Route::get('student-marks/api/questions-by-course', [StudentMarkController::class, 'getQuestionsForCourse'])->name('student-marks.api.questions-course');

Route::resource('student-marks', StudentMarkController::class);

Route::resource('program-outcomes', ProgramOutcomeController::class)->names([
    'index' => 'program-outcomes.index',
    'create' => 'program-outcomes.create',
    'store' => 'program-outcomes.store',
    'show' => 'program-outcomes.show',
    'edit' => 'program-outcomes.edit',
    'update' => 'program-outcomes.update',
    'destroy' => 'program-outcomes.destroy',
]);

// Driver Helper Assignment Report Routes
Route::get('/app/driver-helper-assignment-report', [DriverHelperAssignmentReportController::class, 'index'])->name('driver-helper-assignment-report');
Route::get('/app/driver-helper-assignment-report/print', [DriverHelperAssignmentReportController::class, 'print'])->name('driver-helper-assignment-report.print');
Route::get('/app/driver-helper-assignment-report/pdf', [DriverHelperAssignmentReportController::class, 'pdf'])->name('driver-helper-assignment-report.pdf');
Route::get('/app/driver-helper-assignment-report/excel', [DriverHelperAssignmentReportController::class, 'excel'])->name('driver-helper-assignment-report.excel');

// Driver Trip Report Routes
Route::get('/app/driver-trip-report', [DriverTripReportController::class, 'index'])->name('driver-trip-report');
Route::get('/app/driver-trip-report/print', [DriverTripReportController::class, 'print'])->name('driver-trip-report.print');
Route::get('/app/driver-trip-report/pdf', [DriverTripReportController::class, 'pdf'])->name('driver-trip-report.pdf');
Route::get('/app/driver-trip-report/excel', [DriverTripReportController::class, 'excel'])->name('driver-trip-report.excel');

// Reward Report Routes
Route::get('/app/reward-report', [RewardReportController::class, 'index'])->name('reward-report');
Route::post('/app/reward-report/ajax', [RewardReportController::class, 'ajax'])->name('reward-report.ajax');
Route::get('/app/reward-report/print-list', [RewardReportController::class, 'printList'])->name('reward-report.print-list');
Route::get('/app/reward-report/pdf', [RewardReportController::class, 'pdf'])->name('reward-report.pdf');
Route::get('/app/reward-report/excel', [RewardReportController::class, 'excel'])->name('reward-report.excel');

// Punishment Report Routes
Route::get('/app/punishment-report', [PunishmentReportController::class, 'index'])->name('punishment-report');
Route::post('/app/punishment-report/ajax', [PunishmentReportController::class, 'ajax'])->name('punishment-report.ajax');
Route::get('/app/punishment-report/print-list', [PunishmentReportController::class, 'printList'])->name('punishment-report.print-list');
Route::get('/app/punishment-report/pdf', [PunishmentReportController::class, 'pdf'])->name('punishment-report.pdf');
Route::get('/app/punishment-report/excel', [PunishmentReportController::class, 'excel'])->name('punishment-report.excel');

// Purchase Report Routes
Route::get('/app/purchase-report', [PurchaseReportController::class, 'index'])->name('purchase-report');
Route::post('/app/purchase-report/ajax', [PurchaseReportController::class, 'ajax'])->name('purchase-report.ajax');
Route::get('/app/purchase-report/print-list', [PurchaseReportController::class, 'printList'])->name('purchase-report.print-list');
Route::get('/app/purchase-report/pdf', [PurchaseReportController::class, 'pdf'])->name('purchase-report.pdf');
Route::get('/app/purchase-report/excel', [PurchaseReportController::class, 'excel'])->name('purchase-report.excel');

// Issue Report Routes
Route::get('/app/issue-report', [IssueReportController::class, 'index'])->name('issue-report');
Route::post('/app/issue-report/ajax', [IssueReportController::class, 'ajax'])->name('issue-report.ajax');
Route::get('/app/issue-report/print-list', [IssueReportController::class, 'printList'])->name('issue-report.print-list');
Route::get('/app/issue-report/pdf', [IssueReportController::class, 'pdf'])->name('issue-report.pdf');
Route::get('/app/issue-report/excel', [IssueReportController::class, 'excel'])->name('issue-report.excel');

// Stock Report Routes
Route::get('/app/stock-report', [StockReportController::class, 'index'])->name('stock-report');
Route::post('/app/stock-report/ajax', [StockReportController::class, 'ajax'])->name('stock-report.ajax');
Route::get('/app/stock-report/print-list', [StockReportController::class, 'printList'])->name('stock-report.print-list');
Route::get('/app/stock-report/pdf', [StockReportController::class, 'pdf'])->name('stock-report.pdf');
Route::get('/app/stock-report/excel', [StockReportController::class, 'excel'])->name('stock-report.excel');

// Monthly Salary Settings Routes (with permissions)
Route::get('/app/settings/salary-configuration', [MonthlySalarySettingController::class, 'index'])->name('monthly-salary-settings.index')->middleware('permission:salary-settings-view');
Route::get('/app/settings/salary-configuration/create', [MonthlySalarySettingController::class, 'create'])->name('monthly-salary-settings.create')->middleware('permission:salary-settings-create');
Route::post('/app/settings/salary-configuration', [MonthlySalarySettingController::class, 'store'])->name('monthly-salary-settings.store')->middleware('permission:salary-settings-create');
Route::get('/app/settings/salary-configuration/{monthlySalarySetting}', [MonthlySalarySettingController::class, 'show'])->name('monthly-salary-settings.show')->middleware('permission:salary-settings-view');
Route::get('/app/settings/salary-configuration/{monthlySalarySetting}/edit', [MonthlySalarySettingController::class, 'edit'])->name('monthly-salary-settings.edit')->middleware('permission:salary-settings-edit');
Route::put('/app/settings/salary-configuration/{monthlySalarySetting}', [MonthlySalarySettingController::class, 'update'])->name('monthly-salary-settings.update')->middleware('permission:salary-settings-edit');
Route::delete('/app/settings/salary-configuration/{monthlySalarySetting}', [MonthlySalarySettingController::class, 'destroy'])->name('monthly-salary-settings.destroy')->middleware('permission:salary-settings-delete');
Route::get('/app/settings/salary-configuration-yearly/create', [MonthlySalarySettingController::class, 'createYearly'])->name('monthly-salary-settings.yearly.create')->middleware('permission:salary-settings-manage');
Route::post('/app/settings/salary-configuration-yearly', [MonthlySalarySettingController::class, 'storeYearly'])->name('monthly-salary-settings.yearly.store')->middleware('permission:salary-settings-manage');
Route::post('/app/settings/salary-configuration/calculate', [MonthlySalarySettingController::class, 'calculateSalary'])->name('monthly-salary-settings.calculate')->middleware('permission:salary-settings-view');

// AJAX Routes for Salary Configuration
Route::post('/app/settings/salary-configuration/ajax/store', [MonthlySalarySettingController::class, 'ajaxStore'])->name('monthly-salary-settings.ajax.store');
Route::get('/app/settings/salary-configuration/ajax/{monthlySalarySetting}/edit', [MonthlySalarySettingController::class, 'ajaxEdit'])->name('monthly-salary-settings.ajax.edit');
Route::put('/app/settings/salary-configuration/ajax/{monthlySalarySetting}', [MonthlySalarySettingController::class, 'ajaxUpdate'])->name('monthly-salary-settings.ajax.update');
Route::delete('/app/settings/salary-configuration/ajax/{monthlySalarySetting}', [MonthlySalarySettingController::class, 'ajaxDestroy'])->name('monthly-salary-settings.ajax.destroy');
Route::patch('/app/settings/salary-configuration/ajax/{monthlySalarySetting}/toggle-status', [MonthlySalarySettingController::class, 'ajaxToggleStatus'])->name('monthly-salary-settings.ajax.toggle-status');

// New AJAX routes for enhanced yearly management
Route::get('/app/settings/salary-configuration-yearly/management', [MonthlySalarySettingController::class, 'yearlyManagement'])->name('monthly-salary-settings.yearly.management');
Route::get('/app/settings/salary-configuration-yearly/get-settings', [MonthlySalarySettingController::class, 'getYearlySettings'])->name('monthly-salary-settings.yearly.get-settings');
Route::post('/app/settings/salary-configuration-yearly/update-monthly', [MonthlySalarySettingController::class, 'updateMonthlySetting'])->name('monthly-salary-settings.yearly.update-monthly');
Route::post('/app/settings/salary-configuration-yearly/create-yearly-ajax', [MonthlySalarySettingController::class, 'createYearlySettingsAjax'])->name('monthly-salary-settings.yearly.create-yearly-ajax');
Route::delete('/app/settings/salary-configuration-yearly/delete-monthly', [MonthlySalarySettingController::class, 'deleteMonthlySetting'])->name('monthly-salary-settings.yearly.delete-monthly');

// Salary Sheet Routes (with permissions)
Route::get('/app/reports/salary-sheet', [\App\Http\Controllers\SalarySheetController::class, 'index'])->name('salary-sheet.index')->middleware('permission:salary-sheet-view');
Route::get('/app/reports/salary-sheet/print-list', [\App\Http\Controllers\SalarySheetController::class, 'printList'])->name('salary-sheet.print-list')->middleware('permission:salary-sheet-view');
Route::get('/app/reports/salary-sheet/pdf', [\App\Http\Controllers\SalarySheetController::class, 'exportPdf'])->name('salary-sheet.pdf')->middleware('permission:salary-sheet-export');
Route::get('/app/reports/salary-sheet/excel', [\App\Http\Controllers\SalarySheetController::class, 'exportExcel'])->name('salary-sheet.excel')->middleware('permission:salary-sheet-export');

// Report Routes
Route::get('/app/employee-list-report', [ReportController::class, 'employeeListReport'])->name('employee-list-report');
Route::get('/app/employee-list-report/print-list', [ReportController::class, 'printEmployeeListReport'])->name('employee-list-report.print-list');

// Employee specific routes (MUST be before resource routes)
Route::get('/app/employees/add-employee', [EmployeeController::class, 'create'])->name('employees.add-employee')->middleware('permission:employee-add');
Route::get('/app/employees/view-employee', [EmployeeController::class, 'index'])->name('employees.view-employee');
Route::get('/app/employees/get-data', [EmployeeController::class, 'getData'])->name('employees.get-data');

// Employee resource routes (parameterized routes come after)
Route::resource('employees', EmployeeController::class)->except(['create', 'edit', 'destroy']);
Route::get('/app/employees/{employee}/show', [EmployeeController::class, 'show'])->name('employees.show');
Route::get('/app/employees/{employee}/print', [EmployeeController::class, 'print'])->name('employees.print');
Route::get('/app/employees/{employee}/pdf', [EmployeeController::class, 'pdf'])->name('employees.pdf');
Route::get('/app/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit')->middleware('permission:employee-edit');
Route::delete('/app/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy')->middleware('permission:employee-delete');

// Employee Attendance Routes
Route::get('/app/employee-attendance', [App\Http\Controllers\EmployeeAttendanceController::class, 'index'])->name('employee-attendances.index');
Route::get('/app/employee-attendance/add-employee-attendance', [App\Http\Controllers\EmployeeAttendanceController::class, 'create'])->name('employee-attendances.create');
Route::get('/app/employee-attendance/add-all-attendance', [App\Http\Controllers\EmployeeAttendanceController::class, 'addAllAttendance'])->name('employee-attendances.add-all-attendance');
Route::get('/app/employee-attendance/view-employee-attendance', [App\Http\Controllers\EmployeeAttendanceController::class, 'index'])->name('employee-attendances.view');
Route::post('/app/employee-attendance/submit-all-attendance', [App\Http\Controllers\EmployeeAttendanceController::class, 'submitAllAttendance'])->name('employee-attendances.submit-all-attendance');
Route::post('/app/employee-attendance', [App\Http\Controllers\EmployeeAttendanceController::class, 'store'])->name('employee-attendances.store');
Route::get('/app/employee-attendance/{employeeAttendance}', [App\Http\Controllers\EmployeeAttendanceController::class, 'show'])->name('employee-attendances.show');
Route::get('/app/employee-attendance/{employeeAttendance}/edit', [App\Http\Controllers\EmployeeAttendanceController::class, 'edit'])->name('employee-attendances.edit');
Route::put('/app/employee-attendance/{employeeAttendance}', [App\Http\Controllers\EmployeeAttendanceController::class, 'update'])->name('employee-attendances.update');
Route::delete('/app/employee-attendance/{employeeAttendance}', [App\Http\Controllers\EmployeeAttendanceController::class, 'destroy'])->name('employee-attendances.destroy');

// Color Management Routes (Settings)
Route::get('/app/settings/color', [App\Http\Controllers\settings\Color::class, 'index'])->name('colors.index');
Route::get('/app/settings/get-color', [App\Http\Controllers\settings\Color::class, 'getColor'])->name('colors.get');
Route::post('/app/settings/color', [App\Http\Controllers\settings\Color::class, 'store'])->name('colors.store');
Route::put('/app/settings/color/{id}', [App\Http\Controllers\settings\Color::class, 'update'])->name('colors.update');
Route::delete('/app/settings/color/{id}', [App\Http\Controllers\settings\Color::class, 'destroy'])->name('colors.destroy');

// Fuel Type Management Routes (Settings)
Route::get('/app/settings/fuel-type', [App\Http\Controllers\settings\FuelType::class, 'index'])->name('fuel-type.index');
Route::get('/app/settings/get-fuel-type', [App\Http\Controllers\settings\FuelType::class, 'getFuelType'])->name('fuel-type.get');
Route::post('/app/settings/fuel-type', [App\Http\Controllers\settings\FuelType::class, 'store'])->name('fuel-type.store');
Route::put('/app/settings/fuel-type/{id}', [App\Http\Controllers\settings\FuelType::class, 'update'])->name('fuel-type.update');
Route::delete('/app/settings/fuel-type/{id}', [App\Http\Controllers\settings\FuelType::class, 'destroy'])->name('fuel-type.destroy');

Route::get('/app/settings/users', [User::class, 'index'])->name('app-settings-users');
Route::get('/app/settings/get-users', [User::class, 'getUsers'])->name('app-settings-get-users');
Route::post('/app/settings/users', [User::class, 'store'])->name('app-settings-users.store');
Route::put('/app/settings/users/{id}', [User::class, 'update'])->name('app-settings-users.update');
Route::delete('/app/settings/users/{id}', [User::class, 'destroy'])->name('app-settings-users.destroy');

Route::get('/app/settings/nationality', [Nationality::class, 'index'])->name('app-settings-nationality');
Route::get('/app/settings/get-nationality', [Nationality::class, 'getNationalities'])->name('app-settings-get-nationality');
Route::post('/app/settings/nationality', [Nationality::class, 'store'])->name('app-settings-nationality.store');
Route::put('/app/settings/nationality/{id}', [Nationality::class, 'update'])->name('app-settings-nationality.update');
Route::delete('/app/settings/nationality/{id}', [Nationality::class, 'destroy'])->name('app-settings-nationality.destroy');

Route::get('/app/settings/payment-method', [PaymentMethod::class, 'index'])->name('app-settings-payment-method');
Route::get('/app/settings/get-payment-method', [PaymentMethod::class, 'getPaymentMethods'])->name('app-settings-get-payment-method');
Route::post('/app/settings/payment-method', [PaymentMethod::class, 'store'])->name('app-settings-payment-method.store');
Route::put('/app/settings/payment-method/{id}', [PaymentMethod::class, 'update'])->name('app-settings-payment-method.update');
Route::delete('/app/settings/payment-method/{id}', [PaymentMethod::class, 'destroy'])->name('app-settings-payment-method.destroy');

Route::get('/app/settings/year', [Year::class, 'index'])->name('app-settings-year');
Route::get('/app/settings/get-year', [Year::class, 'getYear'])->name('app-settings-get-year');
Route::post('/app/settings/year', [Year::class, 'store'])->name('app-settings-year.store');
Route::put('/app/settings/year/{id}', [Year::class, 'update'])->name('app-settings-year.update');
Route::delete('/app/settings/year/{id}', [Year::class, 'destroy'])->name('app-settings-year.destroy');

Route::get('/app/settings/app-settings', [AppSettings::class, 'index'])->name('app-settings.index');
Route::put('/app/settings/app-settings/{id}', [AppSettings::class, 'update'])->name('app-settings.update');

Route::get('/app/settings/religion', [Religion::class, 'index'])->name('settings-religion');
Route::get('/app/settings/get-religion', [Religion::class, 'getReligions'])->name('settings-religion.get-religion');
Route::post('/app/settings/religion', [Religion::class, 'store'])->name('settings-religion.store');
Route::put('/app/settings/religion/{id}', [Religion::class, 'update'])->name('settings-religion.update');
Route::delete('/app/settings/religion/{id}', [Religion::class, 'destroy'])->name('settings-religion.destroy');

Route::get('/app/settings/board', [Board::class, 'index'])->name('app-settings-board');
Route::get('/app/settings/get-board', [Board::class, 'getBoard'])->name('app-settings-get-board');
Route::post('/app/settings/board', [Board::class, 'store'])->name('app-settings-board.store');
Route::put('/app/settings/board/{id}', [Board::class, 'update'])->name('app-settings-board.update');
Route::delete('/app/settings/board/{id}', [Board::class, 'destroy'])->name('app-settings-board.destroy');

Route::get('/app/settings/designation', [Designation::class, 'index'])->name('app-settings-designation');
Route::get('/app/settings/get-designation', [Designation::class, 'getDesignation'])->name('app-settings-get-designation');
Route::post('/app/settings/designation', [Designation::class, 'store'])->name('app-settings-designation.store');
Route::put('/app/settings/designation/{id}', [Designation::class, 'update'])->name('app-settings-designation.update');
Route::delete('/app/settings/designation/{id}', [Designation::class, 'destroy'])->name('app-settings-designation.destroy');

// Permission (Settings — permissions table)
Route::get('/app/settings/permission', [PermissionSetting::class, 'index'])->name('app-settings-permission');
Route::get('/app/settings/get-permission', [PermissionSetting::class, 'getPermission'])->name('app-settings-get-permission');
Route::post('/app/settings/permission', [PermissionSetting::class, 'store'])->name('app-settings-permission.store');
Route::put('/app/settings/permission/{id}', [PermissionSetting::class, 'update'])->name('app-settings-permission.update');
Route::delete('/app/settings/permission/{id}', [PermissionSetting::class, 'destroy'])->name('app-settings-permission.destroy');

// Department Routes
Route::get('/app/settings/department', [\App\Http\Controllers\settings\Department::class, 'index'])->name('app-settings-department');
Route::get('/app/settings/get-department', [\App\Http\Controllers\settings\Department::class, 'getDepartment'])->name('app-settings-get-department');
Route::post('/app/settings/department', [\App\Http\Controllers\settings\Department::class, 'store'])->name('app-settings-department.store');
Route::put('/app/settings/department/{id}', [\App\Http\Controllers\settings\Department::class, 'update'])->name('app-settings-department.update');
Route::delete('/app/settings/department/{id}', [\App\Http\Controllers\settings\Department::class, 'destroy'])->name('app-settings-department.destroy');

// Faculty Routes
Route::get('/app/settings/faculty', [Faculty::class, 'index'])->name('faculty');
Route::get('/app/settings/get-faculty', [Faculty::class, 'getFaculty'])->name('app-settings-get-faculty');
Route::post('/app/settings/faculty', [Faculty::class, 'store'])->name('app-settings-faculty.store');
Route::put('/app/settings/faculty/{id}', [Faculty::class, 'update'])->name('app-settings-faculty.update');
Route::delete('/app/settings/faculty/{id}', [Faculty::class, 'destroy'])->name('app-settings-faculty.destroy');

// Teacher (Settings) Routes
Route::get('/app/settings/teacher', [TeacherSettingsController::class, 'index'])->name('app-settings-teacher');
Route::get('/app/settings/get-teacher', [TeacherSettingsController::class, 'getTeacher'])->name('app-settings-get-teacher');
Route::get('/app/settings/get-teacher-designations', [TeacherSettingsController::class, 'getTeacherDesignations'])->name('app-settings-get-teacher-designations');
Route::post('/app/settings/teacher', [TeacherSettingsController::class, 'store'])->name('app-settings-teacher.store');
Route::put('/app/settings/teacher/{id}', [TeacherSettingsController::class, 'update'])->name('app-settings-teacher.update');
Route::delete('/app/settings/teacher/{id}', [TeacherSettingsController::class, 'destroy'])->name('app-settings-teacher.destroy');

// Program Routes
Route::get('/app/settings/program', [Program::class, 'index'])->name('program');
Route::get('/app/settings/get-program', [Program::class, 'getProgram'])->name('app-settings-get-program');
Route::post('/app/settings/program', [Program::class, 'store'])->name('app-settings-program.store');
Route::put('/app/settings/program/{id}', [Program::class, 'update'])->name('app-settings-program.update');
Route::delete('/app/settings/program/{id}', [Program::class, 'destroy'])->name('app-settings-program.destroy');

// Academic Session Routes
Route::get('/app/settings/academic-session', [AcademicSession::class, 'index'])->name('academic-session');
Route::get('/app/settings/get-academic-session', [AcademicSession::class, 'getAcademicSession'])->name('app-settings-get-academic-session');
Route::post('/app/settings/academic-session', [AcademicSession::class, 'store'])->name('app-settings-academic-session.store');
Route::put('/app/settings/academic-session/{id}', [AcademicSession::class, 'update'])->name('app-settings-academic-session.update');
Route::delete('/app/settings/academic-session/{id}', [AcademicSession::class, 'destroy'])->name('app-settings-academic-session.destroy');

// Semester Routes
Route::get('/app/settings/semester', [Semester::class, 'index'])->name('semester');
Route::get('/app/settings/get-semester', [Semester::class, 'getSemester'])->name('app-settings-get-semester');
Route::post('/app/settings/semester', [Semester::class, 'store'])->name('app-settings-semester.store');
Route::put('/app/settings/semester/{id}', [Semester::class, 'update'])->name('app-settings-semester.update');
Route::delete('/app/settings/semester/{id}', [Semester::class, 'destroy'])->name('app-settings-semester.destroy');

// Batch Routes
Route::get('/app/settings/batch', [Batch::class, 'index'])->name('batch');
Route::get('/app/settings/get-batch', [Batch::class, 'getBatch'])->name('app-settings-get-batch');
Route::post('/app/settings/batch', [Batch::class, 'store'])->name('app-settings-batch.store');
Route::put('/app/settings/batch/{id}', [Batch::class, 'update'])->name('app-settings-batch.update');
Route::delete('/app/settings/batch/{id}', [Batch::class, 'destroy'])->name('app-settings-batch.destroy');

// Course Routes
Route::get('/app/settings/course', [Course::class, 'index'])->name('course');
Route::get('/app/settings/get-course', [Course::class, 'getCourse'])->name('app-settings-get-course');
Route::post('/app/settings/course', [Course::class, 'store'])->name('app-settings-course.store');
Route::put('/app/settings/course/{id}', [Course::class, 'update'])->name('app-settings-course.update');
Route::delete('/app/settings/course/{id}', [Course::class, 'destroy'])->name('app-settings-course.destroy');

// Section Routes
Route::get('/app/settings/section', [SectionSettingsController::class, 'index'])->name('section');
Route::get('/app/settings/get-section', [SectionSettingsController::class, 'getSections'])->name('app-settings-get-section');
Route::get('/app/settings/section/departments-by-faculty', [SectionSettingsController::class, 'departmentsByFaculty'])->name('section.departments-by-faculty');
Route::get('/app/settings/section/programs-by-department', [SectionSettingsController::class, 'programsByDepartment'])->name('section.programs-by-department');
Route::get('/app/settings/section/batches-by-program', [SectionSettingsController::class, 'batchesByProgram'])->name('section.batches-by-program');
Route::get('/app/settings/section/semesters-by-program', [SectionSettingsController::class, 'semestersByProgram'])->name('section.semesters-by-program');
Route::post('/app/settings/section', [SectionSettingsController::class, 'store'])->name('app-settings-section.store');
Route::put('/app/settings/section/{id}', [SectionSettingsController::class, 'update'])->name('app-settings-section.update');
Route::delete('/app/settings/section/{id}', [SectionSettingsController::class, 'destroy'])->name('app-settings-section.destroy');

// Grade (marking scale)
Route::get('/app/settings/grade', [Grade::class, 'index'])->name('grade.index');
Route::get('/app/settings/get-grade', [Grade::class, 'getGrades'])->name('grade.data');
Route::post('/app/settings/grade', [Grade::class, 'store'])->name('grade.store');
Route::put('/app/settings/grade/{id}', [Grade::class, 'update'])->name('grade.update');
Route::delete('/app/settings/grade/{id}', [Grade::class, 'destroy'])->name('grade.destroy');

Route::get('/app/settings/item', [Item::class, 'index'])->name('app-settings-item');
Route::get('/app/settings/get-item', [Item::class, 'getItem'])->name('app-settings-get-item');
Route::post('/app/settings/item', [Item::class, 'store'])->name('app-settings-item.store');
Route::put('/app/settings/item/{id}', [Item::class, 'update'])->name('app-settings-item.update');
Route::delete('/app/settings/item/{id}', [Item::class, 'destroy'])->name('app-settings-item.destroy');

Route::get('/app/settings/warehouse', [SettingsWarehouse::class, 'index'])->name('app-settings-warehouse');
Route::get('/app/settings/get-warehouse', [SettingsWarehouse::class, 'getData'])->name('app-settings-get-warehouse');
Route::post('/app/settings/warehouse', [SettingsWarehouse::class, 'store'])->name('app-settings-warehouse.store');
Route::put('/app/settings/warehouse/{id}', [SettingsWarehouse::class, 'update'])->name('app-settings-warehouse.update');
Route::delete('/app/settings/warehouse/{id}', [SettingsWarehouse::class, 'destroy'])->name('app-settings-warehouse.destroy');

// Damage Routes
Route::get('/app/damage/add-damage', [DamageController::class, 'add'])->name('app-damage-add');
Route::get('/app/damage/view-damage', [DamageController::class, 'view'])->name('app-damage-view');
Route::get('/app/damage/edit/{id}', [DamageController::class, 'edit'])->name('app-damage-edit');
Route::get('/app/damage/product-row', [DamageController::class, 'productRow'])->name('app-damage-product-row');
Route::get('/app/damage/get-damage', [DamageController::class, 'getDamage'])->name('app-damage-get');
Route::get('/app/damage/get-items', [DamageController::class, 'getItems'])->name('app-damage-items');
Route::get('/app/damage/get-warehouses', [DamageController::class, 'getWarehouses'])->name('app-damage-warehouses');
Route::get('/app/damage/export-excel', [DamageController::class, 'exportExcel'])->name('app-damage-export-excel');
Route::get('/app/damage/export-pdf', [DamageController::class, 'exportPdf'])->name('app-damage-export-pdf');
Route::post('/app/damage', [DamageController::class, 'store'])->name('app-damage.store');
Route::put('/app/damage/{id}', [DamageController::class, 'update'])->name('app-damage.update');
Route::delete('/app/damage/{id}', [DamageController::class, 'destroy'])->name('app-damage.destroy');

// Bus Schedule Keyword Management Routes (Settings)
Route::get('/app/settings/bus-schedule-keyword', [BusScheduleKeywordController::class, 'index'])->name('app-settings-bus-schedule-keyword');
Route::get('/app/settings/get-bus-schedule-keyword', [BusScheduleKeywordController::class, 'getData'])->name('app-settings-get-bus-schedule-keyword');
Route::post('/app/settings/bus-schedule-keyword', [BusScheduleKeywordController::class, 'store'])->name('app-settings-bus-schedule-keyword.store');
Route::put('/app/settings/bus-schedule-keyword/{id}', [BusScheduleKeywordController::class, 'update'])->name('app-settings-bus-schedule-keyword.update');
Route::delete('/app/settings/bus-schedule-keyword/{id}', [BusScheduleKeywordController::class, 'destroy'])->name('app-settings-bus-schedule-keyword.destroy');

Route::get('/app/settings/punishment-type', [PunishmentType::class, 'index'])->name('app-settings-punishment-type');
Route::get('/app/settings/get-punishment-type', [PunishmentType::class, 'getPunishmentType'])->name('app-settings-get-punishment-type');
Route::post('/app/settings/punishment-type', [PunishmentType::class, 'store'])->name('app-settings-punishment-type.store');
Route::put('/app/settings/punishment-type/{id}', [PunishmentType::class, 'update'])->name('app-settings-punishment-type.update');
Route::delete('/app/settings/punishment-type/{id}', [PunishmentType::class, 'destroy'])->name('app-settings-punishment-type.destroy');

Route::get('/app/settings/reward-type', [RewardType::class, 'index'])->name('app-settings-reward-type');
Route::get('/app/settings/get-reward-type', [RewardType::class, 'getRewardType'])->name('app-settings-get-reward-type');
Route::post('/app/settings/reward-type', [RewardType::class, 'store'])->name('app-settings-reward-type.store');
Route::put('/app/settings/reward-type/{id}', [RewardType::class, 'update'])->name('app-settings-reward-type.update');
Route::delete('/app/settings/reward-type/{id}', [RewardType::class, 'destroy'])->name('app-settings-reward-type.destroy');

Route::get('/app/settings/violation-type', [ViolationType::class, 'index'])->name('app-settings-violation-type');
Route::get('/app/settings/get-violation-type', [ViolationType::class, 'getViolationType'])->name('app-settings-get-violation-type');
Route::post('/app/settings/violation-type', [ViolationType::class, 'store'])->name('app-settings-violation-type.store');
Route::put('/app/settings/violation-type/{id}', [ViolationType::class, 'update'])->name('app-settings-violation-type.update');
Route::delete('/app/settings/violation-type/{id}', [ViolationType::class, 'destroy'])->name('app-settings-violation-type.destroy');

Route::get('/app/settings/bus-sub-type', [BusSubType::class, 'index'])->name('app-settings-bus-sub-type');
Route::get('/app/settings/get-bus-sub-type', [BusSubType::class, 'getBusSubType'])->name('app-settings-get-bus-sub-type');
Route::post('/app/settings/bus-sub-type', [BusSubType::class, 'store'])->name('app-settings-bus-sub-type.store');
Route::put('/app/settings/bus-sub-type/{id}', [BusSubType::class, 'update'])->name('app-settings-bus-sub-type.update');
Route::delete('/app/settings/bus-sub-type/{id}', [BusSubType::class, 'destroy'])->name('app-settings-bus-sub-type.destroy');

Route::get('/app/settings/trip-time', [TripTimeController::class, 'index'])->name('app-settings-trip-time');
Route::get('/app/settings/get-trip-time', [TripTimeController::class, 'getTripTime'])->name('app-settings-get-trip-time');
Route::post('/app/settings/trip-time', [TripTimeController::class, 'store'])->name('app-settings-trip-time.store');
Route::put('/app/settings/trip-time/{id}', [TripTimeController::class, 'update'])->name('app-settings-trip-time.update');
Route::delete('/app/settings/trip-time/{id}', [TripTimeController::class, 'destroy'])->name('app-settings-trip-time.destroy');

Route::get('/app/settings/issuing-authority', [IssuingAuthority::class, 'index'])->name('app-settings-issuing-authority');
Route::get('/app/settings/get-issuing-authority', [IssuingAuthority::class, 'getIssuingAuthority'])->name('app-settings-get-issuing-authority');
Route::post('/app/settings/issuing-authority', [IssuingAuthority::class, 'store'])->name('app-settings-issuing-authority.store');
Route::put('/app/settings/issuing-authority/{id}', [IssuingAuthority::class, 'update'])->name('app-settings-issuing-authority.update');
Route::delete('/app/settings/issuing-authority/{id}', [IssuingAuthority::class, 'destroy'])->name('app-settings-issuing-authority.destroy');

Route::get('/app/settings/status', [Status::class, 'index'])->name('app-settings-status');
Route::get('/app/settings/get-status', [Status::class, 'getStatus'])->name('app-settings-get-status');
Route::post('/app/settings/status', [Status::class, 'store'])->name('app-settings-status.store');
Route::put('/app/settings/status/{id}', [Status::class, 'update'])->name('app-settings-status.update');
Route::delete('/app/settings/status/{id}', [Status::class, 'destroy'])->name('app-settings-status.destroy');

// Related To (Status dropdown source)
Route::get('/app/settings/status-related-to', [RelatedToSettingController::class, 'index'])->name('status-related-to');
Route::get('/app/settings/get-status-related-to', [RelatedToSettingController::class, 'getRelatedTo'])->name('app-settings-get-status-related-to');
Route::post('/app/settings/status-related-to', [RelatedToSettingController::class, 'store'])->name('app-settings-status-related-to.store');
Route::put('/app/settings/status-related-to/{id}', [RelatedToSettingController::class, 'update'])->name('app-settings-status-related-to.update');
Route::delete('/app/settings/status-related-to/{id}', [RelatedToSettingController::class, 'destroy'])->name('app-settings-status-related-to.destroy');

Route::get('/app/settings/deployment-type', [DeploymentType::class, 'index'])->name('app-settings-deployment-type');
Route::get('/app/settings/get-deployment-type', [DeploymentType::class, 'getDeploymentType'])->name('app-settings-get-deployment-type');
Route::post('/app/settings/deployment-type', [DeploymentType::class, 'store'])->name('app-settings-deployment-type.store');
Route::put('/app/settings/deployment-type/{id}', [DeploymentType::class, 'update'])->name('app-settings-deployment-type.update');
Route::delete('/app/settings/deployment-type/{id}', [DeploymentType::class, 'destroy'])->name('app-settings-deployment-type.destroy');

Route::get('/app/settings/educational-qualification', [EducationalQualification::class, 'index'])->name('app-settings-educational-qualification');
Route::get('/app/settings/get-educational-qualification', [EducationalQualification::class, 'getEducationalQualification'])->name('app-settings-get-educational-qualification');
Route::post('/app/settings/educational-qualification', [EducationalQualification::class, 'store'])->name('app-settings-educational-qualification.store');
Route::put('/app/settings/educational-qualification/{id}', [EducationalQualification::class, 'update'])->name('app-settings-educational-qualification.update');
Route::delete('/app/settings/educational-qualification/{id}', [EducationalQualification::class, 'destroy'])->name('app-settings-educational-qualification.destroy');

Route::get('/app/settings/experience-year', [ExperienceYear::class, 'index'])->name('app-settings-experience-year');
Route::get('/app/settings/get-experience-year', [ExperienceYear::class, 'getExperienceYear'])->name('app-settings-get-experience-year');
Route::post('/app/settings/experience-year', [ExperienceYear::class, 'store'])->name('app-settings-experience-year.store');
Route::put('/app/settings/experience-year/{id}', [ExperienceYear::class, 'update'])->name('app-settings-experience-year.update');
Route::delete('/app/settings/experience-year/{id}', [ExperienceYear::class, 'destroy'])->name('app-settings-experience-year.destroy');

// Bus Route Management Routes
Route::get('/app/settings/bus-route', [BusRoute::class, 'index'])->name('app-settings-bus-route');
Route::get('/app/settings/get-bus-route', [BusRoute::class, 'getBusRoutes'])->name('app-settings-get-bus-route');
Route::get('/app/settings/get-stoppages', [BusRoute::class, 'getStoppages'])->name('app-settings-get-stoppages');
Route::post('/app/settings/bus-route', [BusRoute::class, 'store'])->name('app-settings-bus-route.store');
Route::put('/app/settings/bus-route/{id}', [BusRoute::class, 'update'])->name('app-settings-bus-route.update');
Route::delete('/app/settings/bus-route/{id}', [BusRoute::class, 'destroy'])->name('app-settings-bus-route.destroy');

// Bus User Management Routes
Route::get('/app/settings/bus-user', [BusUser::class, 'index'])->name('app-settings-bus-user');
Route::get('/app/settings/get-bus-user', [BusUser::class, 'getBusUser'])->name('app-settings-get-bus-user');
Route::post('/app/settings/bus-user', [BusUser::class, 'store'])->name('app-settings-bus-user.store');
Route::put('/app/settings/bus-user/{id}', [BusUser::class, 'update'])->name('app-settings-bus-user.update');
Route::delete('/app/settings/bus-user/{id}', [BusUser::class, 'destroy'])->name('app-settings-bus-user.destroy');

// Gender Management Routes
Route::get('/app/settings/gender', [Gender::class, 'index'])->name('app-settings-gender');
Route::get('/app/settings/get-gender', [Gender::class, 'getGender'])->name('app-settings-get-gender');
Route::post('/app/settings/gender', [Gender::class, 'store'])->name('app-settings-gender.store');
Route::put('/app/settings/gender/{id}', [Gender::class, 'update'])->name('app-settings-gender.update');
Route::delete('/app/settings/gender/{id}', [Gender::class, 'destroy'])->name('app-settings-gender.destroy');

// Marital Status Management Routes
Route::get('/app/settings/marital-status', [MaritalStatus::class, 'index'])->name('app-settings-marital-status');
Route::get('/app/settings/get-marital-status', [MaritalStatus::class, 'getMaritalStatus'])->name('app-settings-get-marital-status');
Route::post('/app/settings/marital-status', [MaritalStatus::class, 'store'])->name('app-settings-marital-status.store');
Route::put('/app/settings/marital-status/{id}', [MaritalStatus::class, 'update'])->name('app-settings-marital-status.update');
Route::delete('/app/settings/marital-status/{id}', [MaritalStatus::class, 'destroy'])->name('app-settings-marital-status.destroy');

// Color Management Routes
Route::get('/app/settings/color', [App\Http\Controllers\settings\Color::class, 'index'])->name('app-settings-color');
Route::get('/app/settings/get-color', [App\Http\Controllers\settings\Color::class, 'getColor'])->name('app-settings-get-color');
Route::post('/app/settings/color', [App\Http\Controllers\settings\Color::class, 'store'])->name('app-settings-color.store');
Route::put('/app/settings/color/{id}', [App\Http\Controllers\settings\Color::class, 'update'])->name('app-settings-color.update');
Route::delete('/app/settings/color/{id}', [App\Http\Controllers\settings\Color::class, 'destroy'])->name('app-settings-color.destroy');

// Fuel Type Management Routes
Route::get('/app/settings/fuel-type', [App\Http\Controllers\settings\FuelType::class, 'index'])->name('app-settings-fuel-type');
Route::get('/app/settings/get-fuel-type', [App\Http\Controllers\settings\FuelType::class, 'getFuelType'])->name('app-settings-get-fuel-type');
Route::post('/app/settings/fuel-type', [App\Http\Controllers\settings\FuelType::class, 'store'])->name('app-settings-fuel-type.store');
Route::put('/app/settings/fuel-type/{id}', [App\Http\Controllers\settings\FuelType::class, 'update'])->name('app-settings-fuel-type.update');
Route::delete('/app/settings/fuel-type/{id}', [App\Http\Controllers\settings\FuelType::class, 'destroy'])->name('app-settings-fuel-type.destroy');

Route::get('/app/settings/bus-type', [BusType::class, 'index'])->name('app-settings-bus-type');
Route::get('/app/settings/get-bus-type', [BusType::class, 'getBusType'])->name('app-settings-get-bus-type');
Route::post('/app/settings/bus-type', [BusType::class, 'store'])->name('app-settings-bus-type.store');
Route::put('/app/settings/bus-type/{id}', [BusType::class, 'update'])->name('app-settings-bus-type.update');
Route::delete('/app/settings/bus-type/{id}', [BusType::class, 'destroy'])->name('app-settings-bus-type.destroy');

Route::get('/app/settings/brand', [Brand::class, 'index'])->name('app-settings-brand');
Route::get('/app/settings/get-brand', [Brand::class, 'getBrand'])->name('app-settings-get-brand');
Route::post('/app/settings/brand', [Brand::class, 'store'])->name('app-settings-brand.store');
Route::put('/app/settings/brand/{id}', [Brand::class, 'update'])->name('app-settings-brand.update');
Route::delete('/app/settings/brand/{id}', [Brand::class, 'destroy'])->name('app-settings-brand.destroy');

Route::get('/app/settings/unit', [Unit::class, 'index'])->name('app-settings-unit');
Route::get('/app/settings/get-unit', [Unit::class, 'getUnit'])->name('app-settings-get-unit');
Route::post('/app/settings/unit', [Unit::class, 'store'])->name('app-settings-unit.store');
Route::put('/app/settings/unit/{id}', [Unit::class, 'update'])->name('app-settings-unit.update');
Route::delete('/app/settings/unit/{id}', [Unit::class, 'destroy'])->name('app-settings-unit.destroy');

Route::get('/app/settings/stoppage', [Stoppage::class, 'index'])->name('app-settings-stoppage');
Route::get('/app/settings/get-stoppage', [Stoppage::class, 'getStoppage'])->name('app-settings-get-stoppage');
Route::post('/app/settings/stoppage', [Stoppage::class, 'store'])->name('app-settings-stoppage.store');
Route::get('/app/settings/stoppage/get-distance', [Stoppage::class, 'getDistance'])->name('app-settings-stoppage.get-distance');
Route::get('/app/settings/stoppage/{id}', [Stoppage::class, 'show'])->name('app-settings-stoppage.show');
Route::put('/app/settings/stoppage/{id}', [Stoppage::class, 'update'])->name('app-settings-stoppage.update');
Route::delete('/app/settings/stoppage/{id}', [Stoppage::class, 'destroy'])->name('app-settings-stoppage.destroy');

Route::get('/app/settings/supplier', [SettingsSupplier::class, 'index'])->name('app-settings-supplier');
Route::get('/app/settings/get-supplier', [SettingsSupplier::class, 'getSupplier'])->name('app-settings-get-supplier');
Route::post('/app/settings/supplier', [SettingsSupplier::class, 'store'])->name('app-settings-supplier.store');
Route::put('/app/settings/supplier/{id}', [SettingsSupplier::class, 'update'])->name('app-settings-supplier.update');
Route::delete('/app/settings/supplier/{id}', [SettingsSupplier::class, 'destroy'])->name('app-settings-supplier.destroy');

Route::get('/app/settings/employee-type', [EmployeeType::class, 'index'])->name('app-settings-employee-type');
Route::get('/app/settings/get-employee-type', [EmployeeType::class, 'getEmployeeType'])->name('app-settings-get-employee-type');
Route::post('/app/settings/employee-type', [EmployeeType::class, 'store'])->name('app-settings-employee-type.store');
Route::put('/app/settings/employee-type/{id}', [EmployeeType::class, 'update'])->name('app-settings-employee-type.update');
Route::delete('/app/settings/employee-type/{id}', [EmployeeType::class, 'destroy'])->name('app-settings-employee-type.destroy');

Route::get('/app/settings/license-type', [LicenseType::class, 'index'])->name('app-settings-license-type');
Route::get('/app/settings/get-license-type', [LicenseType::class, 'getLicenseType'])->name('app-settings-get-license-type');
Route::post('/app/settings/license-type', [LicenseType::class, 'store'])->name('app-settings-license-type.store');
Route::put('/app/settings/license-type/{id}', [LicenseType::class, 'update'])->name('app-settings-license-type.update');
Route::delete('/app/settings/license-type/{id}', [LicenseType::class, 'destroy'])->name('app-settings-license-type.destroy');

Route::get('/app/settings/blood-group', [BloodGroup::class, 'index'])->name('app-settings-blood-group');
Route::get('/app/settings/get-blood-group', [BloodGroup::class, 'getBloodGroup'])->name('app-settings-get-blood-group');
Route::post('/app/settings/blood-group', [BloodGroup::class, 'store'])->name('app-settings-blood-group.store');
Route::put('/app/settings/blood-group/{id}', [BloodGroup::class, 'update'])->name('app-settings-blood-group.update');
Route::delete('/app/settings/blood-group/{id}', [BloodGroup::class, 'destroy'])->name('app-settings-blood-group.destroy');

Route::get('/app/settings/driver-type', [DriverType::class, 'index'])->name('app-settings-driver-type');
Route::get('/app/settings/get-driver-type', [DriverType::class, 'getDriverType'])->name('app-settings-get-driver-type');
Route::post('/app/settings/driver-type', [DriverType::class, 'store'])->name('app-settings-driver-type.store');
Route::put('/app/settings/driver-type/{id}', [DriverType::class, 'update'])->name('app-settings-driver-type.update');
Route::delete('/app/settings/driver-type/{id}', [DriverType::class, 'destroy'])->name('app-settings-driver-type.destroy');

Route::get('/app/supplier', [Supplier::class, 'index'])->name('app-supplier');
Route::get('/app/supplier/get-supplier', [Supplier::class, 'getSupplier'])->name('app-supplier-get');
Route::post('/app/supplier', [Supplier::class, 'store'])->name('app-supplier.store');
Route::put('/app/supplier/{id}', [Supplier::class, 'update'])->name('app-supplier.update');
Route::delete('/app/supplier/{id}', [Supplier::class, 'destroy'])->name('app-supplier.destroy');

Route::get('/app/purchase/add-purchase', [PurchaseController::class, 'addPurchase'])->name('app-purchase-add');
Route::get('/app/purchase/view-purchase', [PurchaseController::class, 'viewPurchase'])->name('app-purchase-view');
Route::get('/app/purchase/view-details/{id}', [PurchaseController::class, 'viewDetails'])->name('app-purchase-view-details');
Route::get('/app/purchase/edit/{id}', [PurchaseController::class, 'edit'])->name('app-purchase-edit');
Route::get('/app/purchase/export-pdf', [PurchaseController::class, 'exportPdf'])->name('app-purchase-export-pdf');
Route::get('/app/purchase/get-purchase', [PurchaseController::class, 'getPurchase'])->name('app-purchase-get');
Route::get('/app/purchase/get-suppliers', [PurchaseController::class, 'getSuppliers'])->name('app-purchase-suppliers');
Route::get('/app/purchase/get-items', [PurchaseController::class, 'getItems'])->name('app-purchase-items');
Route::get('/app/purchase/product-row', [PurchaseController::class, 'productRow'])->name('app-purchase-product-row');
Route::get('/app/purchase/print-list', [PurchaseController::class, 'printPurchaseList'])->name('app-purchase-print-list');
Route::get('/app/purchase/print/{id}', [PurchaseController::class, 'printPurchase'])->name('app-purchase-print');
Route::post('/app/purchase', [PurchaseController::class, 'store'])->name('app-purchase.store');
Route::put('/app/purchase/{id}', [PurchaseController::class, 'update'])->name('app-purchase.update');
Route::delete('/app/purchase/{id}', [PurchaseController::class, 'destroy'])->name('app-purchase.destroy');

// Issue Routes
Route::get('/app/issue/add-issue', [IssueController::class, 'addIssue'])->name('app-issue-add');
Route::get('/app/issue/view-issue', [IssueController::class, 'viewIssue'])->name('app-issue-view');
Route::get('/app/issue/view-details/{id}', [IssueController::class, 'viewDetails'])->name('app-issue-view-details');
Route::get('/app/issue/edit/{id}', [IssueController::class, 'edit'])->name('app-issue-edit');
Route::get('/app/issue/get-issue', [IssueController::class, 'getIssue'])->name('app-issue-get');
Route::get('/app/issue/get-employees', [IssueController::class, 'getEmployees'])->name('app-issue-employees');
Route::get('/app/issue/get-items', [IssueController::class, 'getItems'])->name('app-issue-items');
Route::get('/app/issue/product-row', [IssueController::class, 'productRow'])->name('app-issue-product-row');
Route::get('/app/issue/print-list', [IssueController::class, 'printIssueList'])->name('app-issue-print-list');
Route::get('/app/issue/export-pdf', [IssueController::class, 'exportPdf'])->name('app-issue-export-pdf');
Route::get('/app/issue/print/{id}', [IssueController::class, 'printIssue'])->name('app-issue-print');
Route::post('/app/issue', [IssueController::class, 'store'])->name('app-issue.store');
Route::put('/app/issue/{id}', [IssueController::class, 'update'])->name('app-issue.update');
Route::delete('/app/issue/{id}', [IssueController::class, 'destroy'])->name('app-issue.destroy');

// Income Routes
Route::get('/app/incomes', [IncomeController::class, 'index'])->name('app-incomes');
Route::get('/app/incomes/create', [IncomeController::class, 'create'])->name('app-incomes.create');
Route::get('/app/incomes/{id}', [IncomeController::class, 'show'])->name('app-incomes.show');
Route::get('/app/incomes/{id}/edit', [IncomeController::class, 'edit'])->name('app-incomes.edit');
Route::get('/app/incomes/get-income-heads', [IncomeController::class, 'getIncomeHeads'])->name('app-incomes-get-income-heads');
Route::get('/app/incomes/get-employees', [IncomeController::class, 'getEmployees'])->name('app-incomes-get-employees');
Route::post('/app/incomes', [IncomeController::class, 'store'])->name('app-incomes.store');
Route::put('/app/incomes/{id}', [IncomeController::class, 'update'])->name('app-incomes.update');
Route::delete('/app/incomes/{id}', [IncomeController::class, 'destroy'])->name('app-incomes.destroy');
Route::get('/app/incomes/export/excel', [IncomeController::class, 'exportExcel'])->name('app-incomes.export-excel');
Route::get('/app/incomes/export/pdf', [IncomeController::class, 'exportPdf'])->name('app-incomes.export-pdf');

Route::get('/app/settings/month', [Month::class, 'index'])->name('app-settings-month');
Route::get('/app/settings/get-month', [Month::class, 'getMonth'])->name('app-settings-get-month');
Route::post('/app/settings/month', [Month::class, 'store'])->name('app-settings-month.store');
Route::put('/app/settings/month/{id}', [Month::class, 'update'])->name('app-settings-month.update');
Route::delete('/app/settings/month/{id}', [Month::class, 'destroy'])->name('app-settings-month.destroy');

Route::get('/app/settings/income-head', [IncomeHead::class, 'index'])->name('app-settings-income-head');
Route::get('/app/settings/get-income-head', [IncomeHead::class, 'getIncomeHead'])->name('app-settings-get-income-head');
Route::post('/app/settings/income-head', [IncomeHead::class, 'store'])->name('app-settings-income-head.store');
Route::put('/app/settings/income-head/{id}', [IncomeHead::class, 'update'])->name('app-settings-income-head.update');
Route::delete('/app/settings/income-head/{id}', [IncomeHead::class, 'destroy'])->name('app-settings-income-head.destroy');

Route::get('/app/settings/expense-head', [ExpenseHead::class, 'index'])->name('app-settings-expense-head');
Route::get('/app/settings/get-expense-head', [ExpenseHead::class, 'getExpenseHead'])->name('app-settings-get-expense-head');
Route::post('/app/settings/expense-head', [ExpenseHead::class, 'store'])->name('app-settings-expense-head.store');
Route::put('/app/settings/expense-head/{id}', [ExpenseHead::class, 'update'])->name('app-settings-expense-head.update');
Route::delete('/app/settings/expense-head/{id}', [ExpenseHead::class, 'destroy'])->name('app-settings-expense-head.destroy');

Route::get('/app/settings/fee-head', [FeeHead::class, 'index'])->name('app-settings-fee-head');
Route::get('/app/settings/get-fee-head', [FeeHead::class, 'getFeeHead'])->name('app-settings-get-fee-head');
Route::post('/app/settings/fee-head', [FeeHead::class, 'store'])->name('app-settings-fee-head.store');
Route::put('/app/settings/fee-head', [FeeHead::class, 'update'])->name('app-settings-fee-head.update');
Route::put('/app/settings/fee-head/{id}', [FeeHead::class, 'update'])->name('app-settings-fee-head.update-by-id');
Route::delete('/app/settings/fee-head/{id}', [FeeHead::class, 'destroy'])->name('app-settings-fee-head.destroy');

Route::get('/app/settings/fee-settings', [FeeSettings::class, 'index'])->name('app-settings-fee-settings');
Route::get('/app/settings/get-fee-settings', [FeeSettings::class, 'getFeeSettings'])->name('app-settings-get-fee-settings');
Route::post('/app/settings/fee-settings', [FeeSettings::class, 'store'])->name('app-settings-fee-settings.store');
Route::put('/app/settings/fee-settings/{id}', [FeeSettings::class, 'update'])->name('app-settings-fee-settings.update');
Route::delete('/app/settings/fee-settings/{id}', [FeeSettings::class, 'destroy'])->name('app-settings-fee-settings.destroy');

// Cache Management Routes
Route::get('/app/settings/cache-clear', [CacheController::class, 'index'])->name('app-settings-cache-clear');
Route::get('/app/settings/clear-cache', [CacheController::class, 'clearCache'])->name('clear-cache');

// Main Page Route
Route::get('/', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::post('/auth/login-basic', [LoginBasic::class, 'login'])->name('auth-login-basic.post');

// Authentication Routes
Route::get('/login', [LoginBasic::class, 'index'])->name('login');
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');

Route::get('/dashboard/analytics', [Analytics::class, 'index'])->name('dashboard-analytics');

Route::get('/layouts/vertical', [Vertical::class, 'index'])->name('dashboard-analytics');

// authentication

Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::get('/auth/register-cover', [RegisterCover::class, 'index'])->name('auth-register-cover');
Route::get('/auth/register-multisteps', [RegisterMultiSteps::class, 'index'])->name('auth-register-multisteps');
Route::get('/auth/verify-email-basic', [VerifyEmailBasic::class, 'index'])->name('auth-verify-email-basic');
Route::get('/auth/verify-email-cover', [VerifyEmailCover::class, 'index'])->name('auth-verify-email-cover');
Route::get('/auth/reset-password-basic', [ResetPasswordBasic::class, 'index'])->name('auth-reset-password-basic');
Route::get('/auth/reset-password-cover', [ResetPasswordCover::class, 'index'])->name('auth-reset-password-cover');
Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');
Route::get('/auth/forgot-password-cover', [ForgotPasswordCover::class, 'index'])->name('auth-forgot-password-cover');
Route::get('/auth/two-steps-basic', [TwoStepsBasic::class, 'index'])->name('auth-two-steps-basic');
Route::get('/auth/two-steps-cover', [TwoStepsCover::class, 'index'])->name('auth-two-steps-cover');

use App\Http\Controllers\BusController;
use App\Http\Controllers\BusHelperController;
use App\Http\Controllers\BusScheduleController;
use App\Http\Controllers\BusTripController;
use App\Http\Controllers\DistanceController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PunishmentController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\RuleController;

// Bus Schedule Management Routes
Route::get('/app/bus-schedules', [BusScheduleController::class, 'index'])->name('bus-schedules.index');
Route::get('/app/bus-schedules/create', [BusScheduleController::class, 'create'])->name('bus-schedules.create');
Route::post('/app/bus-schedules', [BusScheduleController::class, 'store'])->name('bus-schedules.store');

// Bus Schedule Routes (must be before {busSchedule} routes to prevent route conflicts)
Route::get('/app/bus-schedules/create-schedule', [BusScheduleController::class, 'createSchedule'])->name('bus-schedules.create-schedule');
Route::post('/app/bus-schedules/create-schedule', [BusScheduleController::class, 'storeSchedule'])->name('bus-schedules.store-schedule');
Route::get('/app/bus-schedules/schedule-index', [BusScheduleController::class, 'scheduleIndex'])->name('bus-schedules.schedule-index');
Route::get('/app/bus-schedules/schedule/{id}/edit', [BusScheduleController::class, 'editSchedule'])->name('bus-schedules.schedule-edit');
Route::put('/app/bus-schedules/schedule/{id}', [BusScheduleController::class, 'updateSchedule'])->name('bus-schedules.schedule-update');
Route::get('/app/bus-schedules/schedule/{id}/view', [BusScheduleController::class, 'viewSchedule'])->name('bus-schedules.schedule-view');
Route::get('/app/bus-schedules/schedule/{id}/print', [BusScheduleController::class, 'printSchedule'])->name('bus-schedules.schedule-print');
Route::get('/app/bus-schedules/schedule/{id}/pdf', [BusScheduleController::class, 'exportSchedulePdf'])->name('bus-schedules.schedule-pdf');
Route::delete('/app/bus-schedules/schedule/{id}', [BusScheduleController::class, 'destroySchedule'])->name('bus-schedules.schedule-destroy');

// Bus Schedule Additional Routes (must be before {busSchedule} routes)
Route::get('/app/bus-schedules/bus-user/{busUserId}', [BusScheduleController::class, 'busUserSchedules'])->name('bus-schedules.bus-user');
Route::get('/app/bus-schedules/export-pdf', [BusScheduleController::class, 'exportPdf'])->name('bus-schedules.export-pdf');
Route::get('/app/bus-schedules/print', [BusScheduleController::class, 'print'])->name('bus-schedules.print');

// Bus Schedule Conflict Checking Routes
Route::post('/app/bus-schedules/check-driver-conflicts', [BusScheduleController::class, 'checkDriverConflictsAjax'])->name('bus-schedules.check-driver-conflicts');
Route::post('/app/bus-schedules/check-assistant-conflicts', [BusScheduleController::class, 'checkAssistantConflictsAjax'])->name('bus-schedules.check-assistant-conflicts');

// Bus Schedule Trip Times API
Route::get('/app/bus-schedules/trip-times', [BusScheduleController::class, 'getTripTimes'])->name('bus-schedules.trip-times');

// Bus Schedule Resource Routes (with parameters - must be last)
Route::get('/app/bus-schedules/{busSchedule}', [BusScheduleController::class, 'show'])->name('bus-schedules.show');
Route::get('/app/bus-schedules/{busSchedule}/edit', [BusScheduleController::class, 'edit'])->name('bus-schedules.edit');
Route::put('/app/bus-schedules/{busSchedule}', [BusScheduleController::class, 'update'])->name('bus-schedules.update');
Route::delete('/app/bus-schedules/{busSchedule}', [BusScheduleController::class, 'destroy'])->name('bus-schedules.destroy');

Route::get('/app/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
Route::get('/app/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
Route::get('/app/expenses/{id}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
Route::get('/app/expenses/export-excel', [ExpenseController::class, 'exportExcel'])->name('expenses.export-excel');
Route::get('/app/expenses/export-pdf', [ExpenseController::class, 'exportPdf'])->name('expenses.export-pdf');
Route::get('/app/get-expenses', [ExpenseController::class, 'getExpenses'])->name('app-get-expenses');
Route::post('/app/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
Route::put('/app/expenses/{id}', [ExpenseController::class, 'update'])->name('expenses.update');
Route::delete('/app/expenses/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

Route::resource('rules', RuleController::class)->names([
    'index' => 'app-access-rules.index',
    'store' => 'app-access-rules.store',
    'edit' => 'app-access-rules.edit',
    'update' => 'app-access-rules.update',
    'destroy' => 'app-access-rules.destroy',
]);
Route::get('/app/settings/get-rules', [RuleController::class, 'getRules'])->name('get-rules');
Route::resource('permissions', PermissionController::class)->names([
    'index' => 'app-access-permission.index',
    'store' => 'app-access-permission.store',
    'edit' => 'app-access-permission.edit',
    'update' => 'app-access-permission.update',
    'destroy' => 'app-access-permission.destroy',
]);

// Bus Management Routes
Route::get('/app/buses', [BusController::class, 'index'])->name('buses.index');
Route::get('/app/buses/add-bus', [BusController::class, 'create'])->name('buses.create');
Route::get('/app/buses/expired-documents', [BusController::class, 'expiredDocuments'])->name('buses.expired-documents');
Route::get('/app/buses/service-due', [BusController::class, 'serviceDue'])->name('buses.service-due');
Route::get('/app/buses/export', [BusController::class, 'export'])->name('buses.export');
Route::get('/app/bus-list', [BusController::class, 'busList'])->name('bus-list');
Route::post('/app/bus-list/ajax', [BusController::class, 'busList'])->name('bus-list.ajax');
Route::get('/app/bus-list/print-list', [BusController::class, 'printBusList'])->name('bus-list.print-list');
Route::get('/app/bus-list/pdf', [BusController::class, 'busListPdf'])->name('bus-list.pdf');
// Route::get('/app/buses/daily-list', [\App\Http\Controllers\DailyBusListController::class, 'index'])->name('buses.daily-list');

// Specific bus routes (must be before resource routes to prevent conflicts)
Route::get('/app/buses/get-buses-by-type', [BusController::class, 'getBusesByType'])->name('buses.get-buses-by-type');
Route::get('/app/buses/get-buses-by-subtype', [BusController::class, 'getBusesBySubType'])->name('buses.get-buses-by-subtype');
Route::get('/app/buses/get-buses-names-by-type-and-subtype', [BusController::class, 'getBusesNamesByTypeAndSubType'])->name('buses.get-buses-names-by-type-and-subtype');
Route::get('/app/buses/assign-driver-helper-all', [BusController::class, 'assignDriverHelperAll'])->name('buses.assign-driver-helper-all');
Route::post('/app/buses/save-driver-helper-assignment', [BusController::class, 'saveDriverHelperAssignment'])->name('buses.save-driver-helper-assignment');
Route::post('/app/buses/save-all-driver-helper-assignments', [BusController::class, 'saveAllDriverHelperAssignments'])->name('buses.save-all-driver-helper-assignments');

// Fuel Management Routes
Route::get('/app/fuels', [FuelController::class, 'index'])->name('fuels.index');
Route::get('/app/fuels/create', [FuelController::class, 'create'])->name('fuels.create');
Route::post('/app/fuels', [FuelController::class, 'store'])->name('fuels.store');
Route::post('/app/fuels/store-all', [FuelController::class, 'storeAll'])->name('fuels.store-all');
Route::get('/app/fuels/print', [FuelController::class, 'print'])->name('fuels.print');
Route::get('/app/fuels/pdf', [FuelController::class, 'pdf'])->name('fuels.pdf');
Route::get('/app/fuels/{fuel}/edit', [FuelController::class, 'edit'])->name('fuels.edit');
Route::put('/app/fuels/{fuel}', [FuelController::class, 'update'])->name('fuels.update');
Route::delete('/app/fuels/{fuel}', [FuelController::class, 'destroy'])->name('fuels.destroy');

// Lubricant Management Routes
Route::get('/app/lubricants', [LubricantController::class, 'index'])->name('lubricants.index');
Route::get('/app/lubricants/create', [LubricantController::class, 'create'])->name('lubricants.create');
Route::post('/app/lubricants', [LubricantController::class, 'store'])->name('lubricants.store');
Route::post('/app/lubricants/store-all', [LubricantController::class, 'storeAll'])->name('lubricants.store-all');
Route::get('/app/lubricants/print', [LubricantController::class, 'print'])->name('lubricants.print');
Route::get('/app/lubricants/pdf', [LubricantController::class, 'pdf'])->name('lubricants.pdf');
Route::get('/app/lubricants/{lubricant}/edit', [LubricantController::class, 'edit'])->name('lubricants.edit');
Route::put('/app/lubricants/{lubricant}', [LubricantController::class, 'update'])->name('lubricants.update');
Route::delete('/app/lubricants/{lubricant}', [LubricantController::class, 'destroy'])->name('lubricants.destroy');

// Bus resource routes
Route::post('/app/buses', [BusController::class, 'store'])->name('buses.store');
Route::get('/app/buses/{bus}', [BusController::class, 'show'])->name('buses.show');
Route::get('/app/buses/{bus}/edit', [BusController::class, 'edit'])->name('buses.edit');
Route::put('/app/buses/{bus}', [BusController::class, 'update'])->name('buses.update');
Route::delete('/app/buses/{bus}', [BusController::class, 'destroy'])->name('buses.destroy');

// Distance Management Routes
Route::get('/app/distances', [DistanceController::class, 'index'])->name('distances.index');
Route::get('/app/distances/create', [DistanceController::class, 'create'])->name('distances.create');
Route::post('/app/distances', [DistanceController::class, 'store'])->name('distances.store');
Route::get('/app/distances/get-distance', [DistanceController::class, 'getDistance'])->name('distances.get-distance');
Route::get('/app/distances/{distance}', [DistanceController::class, 'show'])->name('distances.show');
Route::get('/app/distances/{distance}/edit', [DistanceController::class, 'edit'])->name('distances.edit');
Route::put('/app/distances/{distance}', [DistanceController::class, 'update'])->name('distances.update');
Route::delete('/app/distances/{distance}', [DistanceController::class, 'destroy'])->name('distances.destroy');

// Punishment Management Routes
Route::resource('punishments', PunishmentController::class);
Route::get('/app/punishments/get-buses-by-type', [PunishmentController::class, 'getBusesByType'])->name('punishments.get-buses-by-type');
Route::get('/app/punishments/get-buses-by-subtype', [PunishmentController::class, 'getBusesBySubType'])->name('punishments.get-buses-by-subtype');
Route::get('/app/punishments/get-drivers', [PunishmentController::class, 'getDrivers'])->name('punishments.get-drivers');

// Reward Management Routes
Route::post('rewards/validate', [RewardController::class, 'validateReward'])->name('rewards.validate');
Route::resource('rewards', RewardController::class);

// Bus Trip Management Routes
Route::get('/app/bus-trips', [BusTripController::class, 'index'])->name('bus-trips.index');
Route::get('/app/bus-trip/add-bus-trip', [BusTripController::class, 'create'])->name('bus-trips.create');
Route::get('/app/bus-trip/add-all-bus-trip', [BusTripController::class, 'addAllBusTrip'])->name('bus-trips.add-all-bus-trip');
Route::get('/app/bus-trip/view-bus-trip', [BusTripController::class, 'viewBusTrip'])->name('bus-trips.view-bus-trip');
Route::post('/app/bus-trip', [BusTripController::class, 'store'])->name('bus-trips.store');
Route::post('/app/bus-trip/own-bus', [BusTripController::class, 'storeOwnBus'])->name('bus-trips.store-own-bus');
Route::post('/app/bus-trip/brtc-bus', [BusTripController::class, 'storeBRTCBus'])->name('bus-trips.store-brtc-bus');
Route::post('/app/bus-trip/hired-bus', [BusTripController::class, 'storeHiredBus'])->name('bus-trips.store-hired-bus');
Route::get('/app/bus-trip/{busTrip}', [BusTripController::class, 'show'])->name('bus-trips.show');
Route::get('/app/bus-trip/{busTrip}/edit', [BusTripController::class, 'edit'])->name('bus-trips.edit');
Route::put('/app/bus-trip/{busTrip}', [BusTripController::class, 'update'])->name('bus-trips.update');
Route::delete('/app/bus-trip/{busTrip}', [BusTripController::class, 'destroy'])->name('bus-trips.destroy');
Route::get('/app/bus-trip/monthly-billing', [BusTripController::class, 'monthlyBilling'])->name('bus-trips.monthly-billing');
Route::get('/app/bus-trip/get-buses-by-type', [BusTripController::class, 'getBusesByType'])->name('bus-trips.get-buses-by-type');
Route::get('/app/bus-trip/get-buses-names-by-subtype', [BusTripController::class, 'getBusesNamesBySubType'])->name('bus-trips.get-buses-names-by-subtype');
Route::post('/app/bus-trip/get-trip-numbers-for-date', [BusTripController::class, 'getTripNumbersForDate'])->name('bus-trips.get-trip-numbers-for-date');
Route::get('/app/trip-report', [BusTripController::class, 'tripReport'])->name('trip-report');
Route::post('/app/trip-report/ajax', [BusTripController::class, 'tripReport'])->name('trip-report.ajax');
Route::get('/app/trip-report/print-list', [BusTripController::class, 'printTripReport'])->name('trip-report.print-list');
Route::get('/app/trip-report/pdf', [BusTripController::class, 'exportTripReportPdf'])->name('trip-report.pdf');

// Bus Requisition Management Routes
Route::get('/app/bus-requisitions', [BusRequisitionController::class, 'index'])->name('app-bus-requisitions');
Route::get('/app/bus-requisitions/create', [BusRequisitionController::class, 'create'])->name('app-bus-requisitions.create');
Route::post('/app/bus-requisitions', [BusRequisitionController::class, 'store'])->name('app-bus-requisitions.store');
Route::get('/app/bus-requisitions/{id}', [BusRequisitionController::class, 'show'])->name('app-bus-requisitions.show');
Route::get('/app/bus-requisitions/{id}/print', [BusRequisitionController::class, 'print'])->name('app-bus-requisitions.print');
Route::get('/app/bus-requisitions/{id}/pdf', [BusRequisitionController::class, 'pdf'])->name('app-bus-requisitions.pdf');
Route::get('/app/bus-requisitions/{id}/edit', [BusRequisitionController::class, 'edit'])->name('app-bus-requisitions.edit');
Route::put('/app/bus-requisitions/{id}', [BusRequisitionController::class, 'update'])->name('app-bus-requisitions.update');
Route::patch('/app/bus-requisitions/{id}/status', [BusRequisitionController::class, 'updateStatus'])->name('app-bus-requisitions.update-status');
Route::delete('/app/bus-requisitions/{id}', [BusRequisitionController::class, 'destroy'])->name('app-bus-requisitions.destroy');

// Bus Requisition API Routes
Route::post('/api/bus-requisitions', [BusRequisitionController::class, 'apiStore'])->name('api-bus-requisitions.store');
Route::get('/api/bus-requisitions', [BusRequisitionController::class, 'apiIndex'])->name('api-bus-requisitions.index');
Route::get('/api/departments', [BusRequisitionController::class, 'apiGetDepartments'])->name('api-departments.index');

// Bus Requisition API Documentation Routes
Route::get('/app/bus-requisitions/api-doc/post', [BusRequisitionController::class, 'apiDocPost'])->name('app-bus-requisitions.api-doc-post');
Route::get('/app/bus-requisitions/api-doc/get', [BusRequisitionController::class, 'apiDocGet'])->name('app-bus-requisitions.api-doc-get');

// Driver Management Routes
Route::get('/app/drivers', [DriverController::class, 'index'])->name('drivers.index');
Route::get('/app/drivers/create', [DriverController::class, 'create'])->name('drivers.create');
Route::get('/app/drivers/add-driver', [DriverController::class, 'create'])->name('drivers.add-driver');
Route::get('/app/drivers/check-incomplete', [DriverController::class, 'checkIncomplete'])->name('drivers.check-incomplete');
Route::get('/app/drivers/get-data', [DriverController::class, 'getData'])->name('drivers.get-data');
Route::post('/app/drivers', [DriverController::class, 'store'])->name('drivers.store');
Route::post('/app/drivers/save-progress', [DriverController::class, 'saveProgress'])->name('drivers.save-progress');
Route::get('/app/drivers/{driver}', [DriverController::class, 'show'])->name('drivers.show');
Route::get('/app/drivers/{driver}/print', [DriverController::class, 'print'])->name('drivers.print');
Route::get('/app/drivers/{driver}/pdf', [DriverController::class, 'pdf'])->name('drivers.pdf');
Route::get('/app/drivers/{driver}/edit', [DriverController::class, 'edit'])->name('drivers.edit');
Route::put('/app/drivers/{driver}', [DriverController::class, 'update'])->name('drivers.update');
Route::delete('/app/drivers/{driver}', [DriverController::class, 'destroy'])->name('drivers.destroy');

// Assistant Management Routes
// Bus Helper Routes
Route::get('/app/bus-helpers', [BusHelperController::class, 'index'])->name('bus-helpers.index');
Route::get('/app/bus-helpers/add-bus-helper', [BusHelperController::class, 'create'])->name('bus-helpers.create');
Route::get('/app/bus-helpers/view-bus-helper', [BusHelperController::class, 'index'])->name('bus-helpers.view-bus-helper');

// Bus Helper AJAX Routes (MUST be before parameterized routes)
Route::get('/app/bus-helpers/get-data', [BusHelperController::class, 'getBusHelpersData'])->name('bus-helpers.get-data');

// Assistant Resource Routes (parameterized routes MUST come after specific routes)
Route::post('/app/bus-helpers', [BusHelperController::class, 'store'])->name('bus-helpers.store');
Route::get('/app/bus-helpers/{busHelper}', [BusHelperController::class, 'show'])->name('bus-helpers.show');
Route::get('/app/bus-helpers/{busHelper}/edit', [BusHelperController::class, 'edit'])->name('bus-helpers.edit');
Route::get('/app/bus-helpers/{busHelper}/details', [BusHelperController::class, 'getBusHelperDetails'])->name('bus-helpers.get-details');
Route::put('/app/bus-helpers/{busHelper}', [BusHelperController::class, 'update'])->name('bus-helpers.update');
Route::delete('/app/bus-helpers/{busHelper}', [BusHelperController::class, 'destroy'])->name('bus-helpers.destroy');

// Daily Bus List Routes - Custom routes first to avoid conflicts with resource routes
Route::get('/app/daily-bus-lists/add-all-buses-list', [DailyBusListController::class, 'create_all_buses_list'])->name('daily-bus-lists.create-all-buses-list');
Route::post('/app/daily-bus-lists/store-multiple', [DailyBusListController::class, 'storeMultiple'])->name('daily-bus-lists.store-multiple');
Route::get('/app/daily-bus-lists/all-buses-list', [DailyBusListController::class, 'allBusesList'])->name('daily-bus-lists.all-buses-list');
Route::get('/app/daily-bus-lists/data', [DailyBusListController::class, 'getData'])->name('daily-bus-lists.data');
Route::get('/app/daily-bus-lists/all-buses-data', [DailyBusListController::class, 'getAllBusesData'])->name('daily-bus-lists.all-buses-data');
Route::get('/app/daily-bus-lists/filter-options', [DailyBusListController::class, 'getFilterOptions'])->name('daily-bus-lists.filter-options');
Route::get('/app/daily-bus-lists/all-buses-filter-options', [DailyBusListController::class, 'getAllBusesFilterOptions'])->name('daily-bus-lists.all-buses-filter-options');
Route::get('/app/daily-bus-lists/last-saved-data', [DailyBusListController::class, 'getLastSavedData'])->name('daily-bus-lists.last-saved-data');
Route::get('/app/daily-bus-lists/check-bus-data', [DailyBusListController::class, 'checkBusData'])->name('daily-bus-lists.check-bus-data');
Route::get('/app/daily-bus-lists/export-pdf', [DailyBusListController::class, 'exportPdf'])->name('daily-bus-lists.export-pdf');
Route::get('/app/daily-bus-lists/export-excel', [DailyBusListController::class, 'exportExcel'])->name('daily-bus-lists.export-excel');
Route::get('/app/daily-bus-lists/get-filtered-data', [DailyBusListController::class, 'getFilteredData'])->name('daily-bus-lists.get-filtered-data');

Route::get('/app/daily-bus-lists/get-buses-by-subtype', [DailyBusListController::class, 'getBusesBySubType'])
    ->name('daily-bus-lists.get-buses-by-subtype');

Route::get('/app/daily-bus-lists/get-buses-names-by-subtype', [DailyBusListController::class, 'getBusesNamesBySubType'])
    ->name('daily-bus-lists.get-buses-names-by-subtype');

// Test route to check if AJAX is working
Route::get('/app/daily-bus-lists/test-ajax', function () {
    return response()->json(['message' => 'AJAX is working']);
})->name('daily-bus-lists.test-ajax');

// Test route for getBusesBySubType
Route::get('/app/daily-bus-lists/test-buses', function () {
    return response()->json(['message' => 'Buses endpoint is accessible']);
})->name('daily-bus-lists.test-buses');

// Monthly Bill Management Routes
Route::get('/app/monthly-bill', [MonthlyBillController::class, 'index'])->name('monthly-bills.index');
Route::get('/app/monthly-bill/print-list', [MonthlyBillController::class, 'printList'])->name('monthly-bills.print-list');
Route::get('/app/monthly-bill/pdf', [MonthlyBillController::class, 'exportPdf'])->name('monthly-bills.pdf');
Route::get('/app/monthly-bill/create', [MonthlyBillController::class, 'create'])->name('monthly-bills.create');
Route::post('/app/monthly-bill/generate', [MonthlyBillController::class, 'generate'])->name('monthly-bills.generate');
Route::post('/app/monthly-bill/generate-all', [MonthlyBillController::class, 'generateAll'])->name('monthly-bills.generate-all');
Route::get('/app/monthly-bill/{busId}', [MonthlyBillController::class, 'show'])->name('monthly-bills.show');
Route::put('/app/monthly-bill/{monthlyBill}/status', [MonthlyBillController::class, 'updateStatus'])->name('monthly-bills.update-status');
Route::get('/app/monthly-bill/summary', [MonthlyBillController::class, 'getSummary'])->name('monthly-bills.summary');
Route::get('/app/monthly-bill/export', [MonthlyBillController::class, 'export'])->name('monthly-bills.export');

// Resource routes after custom routes
Route::resource('app/daily-bus-lists', DailyBusListController::class);

// Daily Deployment Plan Routes
Route::get('/app/deployment-plans/create-daily-deployment-plan', [DeploymentPlanController::class, 'create'])->name('deployment-plans.create-daily-deployment-plan');
Route::get('/app/deployment-plans/view-daily-deployment-plan', [DeploymentPlanController::class, 'index'])->name('deployment-plans.view-daily-deployment-plan');
Route::get('/app/deployment-plans/get-buses-by-subtype', [DeploymentPlanController::class, 'getBusesBySubType'])->name('deployment-plans.get-buses-by-subtype');
Route::get('/app/deployment-plans/get-last-plan', [DeploymentPlanController::class, 'getLastPlan'])->name('deployment-plans.get-last-plan');
Route::post('/app/deployment-plans/{deploymentPlan}/clone', [DeploymentPlanController::class, 'clonePlan'])->name('deployment-plans.clone');
Route::get('/app/deployment-plans/{deploymentPlan}/pdf', [DeploymentPlanController::class, 'pdf'])->name('deployment-plans.pdf');
Route::resource('app/deployment-plans', DeploymentPlanController::class)->except(['create', 'index']);

// Driver Helper Assignment Routes
Route::resource('app/driver-helper-assignments', DriverHelperAssignmentController::class);
