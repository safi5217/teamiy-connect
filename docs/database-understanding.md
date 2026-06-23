# Teamiy Connect Database Understanding

This project is the employee-side Laravel 13 app for the existing Teamiy HR database.
The SQL dump path outside the workspace could not be opened by the sandbox, but the local
MySQL database configured in `.env` (`teamiy_connect`) is already loaded and was inspected
directly.

## Application Boundary

- Employee-side accounts live in `users`.
- Admin-side accounts live in `admins`.
- The employee app should authenticate against `users`, not `admins`.
- Employee data should be scoped primarily by `users.id`, then by `company_id`, `branch_id`,
  and `department_id` wherever those fields exist.
- The database does not expose foreign key constraints in `information_schema`, even though
  many indexes are named like foreign keys. Relationships should be modeled carefully in
  Eloquent from column names and admin-side behavior.

## Current Project State

- Laravel framework version: `^13.8`.
- The current `User` model only fills `name`, `email`, and `password`; it does not yet match
  the existing employee table.
- `AuthController@login` is still a stub that dumps the request.
- `routes/web.php` currently reads all `users` on `/`, which should be replaced before real use.
- The default Laravel migrations are pending against the shared database:
  - `0001_01_01_000000_create_users_table`
  - `0001_01_01_000001_create_cache_table`
  - `0001_01_01_000002_create_jobs_table`
- Those default migrations should not be run blindly because the shared database already has
  admin-side tables and migration history.

## Employee Identity

Primary table: `users`

Important fields:

- Login/contact: `email`, `work_email`, `username`, `password`, `remember_token`
- Profile: `name`, `avatar`, `dob`, `gender`, `phone`, `address`, `employee_code`
- Status: `status`, `is_active`, `deleted_at`, `online_status`
- Organization: `company_id`, `branch_id`, `department_id`, `post_id`, `supervisor_id`,
  `office_time_id`, `admin_id`
- Employment: `joining_date`, `employment_type`, `user_type`, `contract_start_date`,
  `contract_end_date`, `contract_type`, `pay_grade`
- Attendance device: `nfc_card`

Observed data notes:

- Employee passwords are bcrypt hashes.
- All current employee rows have `email`, `work_email`, `username`, `company_id`, `branch_id`,
  and `department_id`.
- `employment_type` contains mixed values: normal employment values such as `permanent`,
  `temporary`, and `contract`, plus shift-hour text such as `9:00 - 18:00`. Do not rely on this
  field alone for business rules.

Likely Eloquent relationships:

- `User belongsTo Company`
- `User belongsTo Branch`
- `User belongsTo Department`
- `User belongsTo Post`
- `User belongsTo OfficeTime`
- `User belongsTo Supervisor` through `users.supervisor_id`
- `User hasOne EmployeeAccount`
- `User hasOne/hasMany EmployeeSalary`
- `User hasMany Attendance`
- `User hasMany LeaveRequest` through `leave_requests_master.requested_by`

## Organization Tables

- `companies`: tenant/company profile, owner/admin references, logo, contact details, weekend,
  country/currency preferences, active flag.
- `branches`: company branches, branch head, address, phone, optional geolocation.
- `departments`: department per company/branch, department head, active flag.
- `posts`: job/designation records linked to department, branch, and company.
- `office_times`: shifts and attendance rules such as opening/closing time, early/late check-in
  and check-out thresholds.

## Attendance Module

Main tables:

- `attendances`: daily attendance by `user_id` and `company_id`; stores date, check-in/out time,
  location, attendance type, worked hours, overtime, undertime, notes, and office time.
- `attendance_logs`: device/log-style events by `employee_id`, `attendance_type`, and
  `identifier`.
- `qr_attendances` and `nfc_attendances`: check-in helpers.
- `attendance_machines`: attendance devices by company/branch.
- `holidays`: company holidays with public-holiday flag.
- `time_leaves`: short leave/time-off requests by `requested_by`, with issue date, time range,
  status, reason, admin remark, branch, department, and company.

Observed statuses:

- `attendances.attendance_status`: `1`
- `time_leaves.status`: `pending`, `accepted`, `approved`, `rejected`

Employee app screens:

- Today check-in/check-out
- My attendance history
- My overtime/undertime
- Request short time leave
- Holiday calendar

## Leave Module

Main tables:

- `leave_types`: company/branch leave types, allocation, gender applicability, early-exit flag.
- `employee_leave_types`: per-employee leave allocation.
- `leave_requests_master`: actual leave request records by `requested_by`, with leave dates,
  leave type, status, reason, document, admin remark, company/branch/department, and referral.
- `leave_request_approvals`: approval trail by leave request, approver, status, type, and reason.
- `leave_approvals`, `leave_approval_processes`, `leave_approval_departments`,
  `leave_approval_roles`, `leave_approval_notification_recipients`: approval configuration.

Observed statuses:

- `leave_requests_master.status`: `pending`, `approved`

Employee app screens:

- Leave balance
- Request leave
- Leave request history
- Approval progress/status

## Payroll And Finance

Main tables:

- `employee_accounts`: bank/account/salary settings by `user_id`.
- `employee_salaries`: salary configuration by `employee_id`, including annual/monthly/weekly,
  hourly options, tax, overtime, and payment type.
- `generated_payrolls`: generated payroll summary by `employee_id`, branch, department, status,
  salary range, hours, deductions, and net salary.
- `employee_payslips` and `employee_payslip_details`: payslip records and component lines.
- `advance_salaries` and `advance_salary_attachments`: advance salary requests.
- `tadas` and `tada_attachments`: travel/expense claims.
- `salary_groups`, `salary_components`, `salary_group_component`, `salary_group_employees`,
  `salary_revise_histories`, `salary_t_d_s`, `tax_reports`, and related tax detail tables:
  payroll setup and reporting.

Observed statuses:

- `generated_payrolls.status`: `pending`
- `tadas.status`: `pending`, `accepted`, `rejected`

Employee app screens:

- My salary/account details
- My payroll history
- Payslip view/download
- Request advance salary
- TADA/expense claim submission and history

## Projects And Tasks

Main tables:

- `projects`: project details, client, dates, status, priority, branch, departments stored in
  `department_ids`, documents, and cover image.
- `project_team_leaders`: project leaders by `leader_id`.
- `assigned_members`: polymorphic-style assignments with `assignable_type` of `project` or
  `task`, `assignable_id`, and `member_id`.
- `tasks`: task records by project, priority, status, dates, document, branch, and reason.
- `task_checklists`: checklist items by task and assigned employee.
- `task_comments`, `comment_replies`, `mentioned_comment_members`, `attachments`: collaboration
  records.

Observed statuses:

- `projects.status`: `not_started`, `in_progress`, `completed`
- `tasks.status`: `not_started`, `in_progress`, `blocker`, `completed`

Employee app screens:

- My projects
- My tasks
- Task details, checklist, comments, and attachments

## Communication And Company Content

Main tables:

- `notices` and `notice_receivers`: notices by company/branch and target employee/receiver.
- `notifications` and `user_notifications`: notification records and per-user seen status.
- `events`, `event_users`, `event_departments`: events targeted by user or department.
- `team_meetings` and `team_meeting_members`: meetings and participants.
- `company_content_management`: company content pages/policies.

Employee app screens:

- Notice board
- Notifications inbox
- Events calendar
- Meetings
- Company policies/content

## Assets And Documents

Main tables:

- `assets`: company assets, type, code, serial number, warranty, availability, branch.
- `asset_types`: asset categories.
- `asset_assignments`: assigned assets by `user_id`, asset, status, dates, condition, branch,
  and department.
- `employee_documents`: contract/document paths by employee.
- `attachments`: polymorphic attachments for projects and tasks.

Observed statuses:

- `asset_assignments.status`: `assigned`, `returned`

Employee app screens:

- My assigned assets
- My documents/contracts

## Requests And HR Actions

Main tables:

- `complaints`, `complaint_employees`, `complaint_departments`, `complaint_responses`
- `warnings`, `warning_employees`, `warning_departments`, `warning_responses`
- `resignations`
- `terminations`
- `transfers`
- `promotions`
- `awards`
- `trainings`, `training_types`, `trainers`, and related training assignment tables

Employee app screens:

- Submit complaint
- View warnings/responses
- Submit/view resignation
- View transfer, termination, promotion, award, and training records

## Roles And Permissions

The database uses Spatie-style tables:

- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

Observed role guards:

- Admin roles use `guard_name = admin`.
- Employee-side roles use `guard_name = web`.
- `model_has_roles.model_type` contains `user` and `admin`, not fully qualified Laravel class
  names. This may require custom Spatie configuration or careful compatibility handling if the
  employee app uses the package.

## Schema Risks To Handle Before Building

- Many tables have no actual foreign key constraints. Use application-level relationship scoping
  and validation.
- `users` and several later tables have an `id` column but no primary key or auto-increment in
  MySQL. Existing rows have unique IDs, but inserts into those tables may require manual ID
  generation or a database cleanup migration approved by the admin-side owner.
- Do not run the employee app's default Laravel migrations against the shared database.
- Some relation fields are inconsistent:
  - Employee references can be `user_id`, `employee_id`, `requested_by`, `created_by`, or
    `member_id`.
  - Project departments are stored as `department_ids` long text.
  - Some status fields use values like `accepted`; others use `approved`.
- Treat shared-table writes conservatively. Read-only screens should come first, then write flows
  can be added after matching the admin-side insert/update conventions.

## Recommended Build Order

1. Replace starter auth with employee login against `users`, supporting email/work email/username.
2. Update `User` model casts, fillable/guarded strategy, soft deletes, and relationships.
3. Add read-only dashboard using scoped counts for attendance, leave, tasks, notices, payroll,
   and assets.
4. Add read-only profile, attendance history, leave history, payroll, assets, notices, projects,
   and tasks.
5. Add employee write flows one by one: attendance check-in/out, leave request, time leave,
   TADA, advance salary, complaint, and resignation.
6. Add authorization policies/scopes so employees cannot read another employee's records.
7. Add focused Pest tests for auth, scoping, and the first write flows.
