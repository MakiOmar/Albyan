# User Management & Role Assignment Guide - Rocket LMS

This guide explains how users are added with their roles in the Rocket LMS system, including all user types, management interfaces, and role assignment processes.

## 🎯 **User Roles Overview**

### **Available Roles:**
1. **Admin** (`admin`) - System administrators
2. **User** (`user`) - Students/learners
3. **Teacher** (`teacher`) - Course instructors
4. **Organization** (`organization`) - Educational institutions

### **Role IDs (from database seeder):**
```php
// From database/seeders/RolesTableSeeder.php
Role::updateOrCreate(['id' => 1], ['name' => 'user', 'caption' => 'User role']);
Role::updateOrCreate(['id' => 2], ['name' => 'admin', 'caption' => 'Admin role']);
Role::updateOrCreate(['id' => 3], ['name' => 'organization', 'caption' => 'Organization role']);
Role::updateOrCreate(['id' => 4], ['name' => 'teacher', 'caption' => 'Teacher role']);
```

## 🚀 **User Registration Methods**

### **1. Public Registration (Frontend)**

**File:** `app/Http/Controllers/Auth/RegisterController.php`

**Process:**
1. User fills registration form
2. System validates input
3. Role is assigned based on `account_type`:
   ```php
   $roleName = Role::$user; // Default
   $roleId = Role::getUserRoleId();
   
   if ($data['account_type'] == Role::$teacher) {
       $roleName = Role::$teacher;
       $roleId = Role::getTeacherRoleId();
   } else if ($data['account_type'] == Role::$organization) {
       $roleName = Role::$organization;
       $roleId = Role::getOrganizationRoleId();
   }
   ```

**User Creation:**
```php
$user = User::create([
    'role_name' => $roleName,
    'role_id' => $roleId,
    'mobile' => $data['mobile'] ?? null,
    'email' => $data['email'] ?? null,
    'full_name' => $data['full_name'],
    'status' => User::$pending, // Requires verification
    'password' => Hash::make($data['password']),
    'affiliate' => $usersAffiliateStatus,
    'timezone' => $data['timezone'] ?? null,
    'created_at' => time()
]);
```

### **2. Admin Panel User Creation**

**File:** `app/Http/Controllers/Admin/UserController.php`

**Process:**
1. Admin navigates to `/admin/users/create`
2. Fills user creation form
3. Selects role from dropdown
4. User is created with selected role

**User Creation:**
```php
$user = User::create([
    'full_name' => $data['full_name'],
    'role_name' => $role->name,
    'role_id' => $data['role_id'],
    $username => $data[$username],
    'password' => User::generatePassword($data['password']),
    'status' => $data['status'],
    'affiliate' => $usersAffiliateStatus,
    'verified' => true, // Admin-created users are verified
    'created_at' => time(),
]);
```

### **3. Organization User Creation**

**File:** `app/Http/Controllers/Panel/UserController.php`

**Process:**
1. Organization admin creates users
2. Role is automatically assigned:
   - `instructors` → `teacher` role
   - `students` → `user` role

**User Creation:**
```php
$role_name = ($user_type == 'instructors') ? Role::$teacher : Role::$user;
$role_id = ($user_type == 'instructors') ? Role::getTeacherRoleId() : Role::getUserRoleId();

$user = User::create([
    'role_name' => $role_name,
    'role_id' => $role_id,
    'email' => $data['email'],
    'organ_id' => $organization->id, // Links to organization
    'password' => Hash::make($data['password']),
    'full_name' => $data['full_name'],
    'mobile' => $data['mobile'],
    'created_at' => time()
]);
```

### **4. API Registration**

**File:** `app/Http/Controllers/Api/Auth/RegisterController.php`

**Process:**
1. Mobile app sends registration data
2. System creates user with default `user` role
3. Additional steps for profile completion

## 📊 **User Management Interfaces**

### **1. Admin Panel User Lists**

#### **Students List** (`/admin/students`)
- **Controller:** `Admin\UserController@students`
- **View:** `resources/views/admin/users/students.blade.php`
- **Features:**
  - Search by name
  - Filter by date range
  - Filter by organization
  - Filter by user group
  - Filter by status
  - Export to Excel
  - Statistics cards

#### **Instructors List** (`/admin/instructors`)
- **Controller:** `Admin\UserController@instructors`
- **View:** `resources/views/admin/users/instructors.blade.php`
- **Features:**
  - Same filters as students
  - Instructor-specific statistics
  - Course creation permissions

#### **Organizations List** (`/admin/organizations`)
- **Controller:** `Admin\UserController@organizations`
- **View:** `resources/views/admin/users/organizations.blade.php`
- **Features:**
  - Organization management
  - Teacher/student counts per organization

#### **Staff List** (`/admin/staffs`)
- **Controller:** `Admin\UserController@staffs`
- **View:** `resources/views/admin/users/staffs.blade.php`
- **Features:**
  - Admin users only
  - Role-based permissions

### **2. User Creation Forms**

#### **Admin User Creation** (`/admin/users/create`)
- **Controller:** `Admin\UserController@create`
- **View:** `resources/views/admin/users/create.blade.php`
- **Fields:**
  - Full name
  - Email/Mobile
  - Password
  - Role selection (dropdown)
  - Status
  - User group (optional)

#### **User Edit** (`/admin/users/{id}/edit`)
- **Controller:** `Admin\UserController@edit`
- **View:** `resources/views/admin/users/edit.blade.php`
- **Features:**
  - Role change capability
  - Profile information
  - Financial settings
  - Badges management
  - Form fields customization

## 🔧 **Role Assignment Logic**

### **Role Constants (from Role model):**
```php
static $admin = 'admin';
static $user = 'user';
static $teacher = 'teacher';
static $organization = 'organization';
```

### **Role Helper Methods:**
```php
// Get role IDs
Role::getUserRoleId()      // Returns 1
Role::getTeacherRoleId()   // Returns 4
Role::getOrganizationRoleId() // Returns 3
```

### **User Role Methods (from User model):**
```php
$user->isAdmin()         // Check if admin
$user->isUser()          // Check if student
$user->isTeacher()       // Check if instructor
$user->isOrganization()  // Check if organization
```

## 📋 **User Status Management**

### **User Statuses:**
```php
static $active = 'active';
static $pending = 'pending';
static $inactive = 'inactive';
```

### **Status Assignment:**
- **Public Registration:** `pending` (requires verification)
- **Admin Creation:** `active` (immediately verified)
- **Organization Creation:** `active` (immediately verified)

## 🎨 **User Interface Components**

### **Role Selection Dropdown:**
```blade
<select name="role_id" class="form-control">
    <option disabled selected>{{ trans('admin/main.select_role') }}</option>
    @foreach ($roles as $role)
        <option value="{{ $role->id }}">{{ $role->name }} - {{ $role->caption }}</option>
    @endforeach
</select>
```

### **User List Table Columns:**
- ID
- Name (with avatar)
- Contact info (email/mobile)
- Role
- Status
- Registration date
- Actions (edit, delete, impersonate)

### **Filter Options:**
- Search by name
- Date range
- Organization
- User group
- Status (active, inactive, banned, verified)
- Role-specific filters

## 🔐 **Permission System**

### **Role-Based Permissions:**
```php
$user->hasPermission($section_name)
```

### **Permission Checks:**
```php
@can('admin_users_create')
    // Show create button
@endcan

@can('admin_users_edit')
    // Show edit button
@endcan

@can('admin_users_delete')
    // Show delete button
@endcan
```

## 📈 **User Statistics**

### **Dashboard Cards:**
- Total users by role
- Active/inactive counts
- Organization-linked users
- Banned users

### **Export Functionality:**
- Excel export for user lists
- Filtered data export
- Custom date ranges

## 🛠️ **Customization Points**

### **Adding New Roles:**
1. Add role to database seeder
2. Update Role model constants
3. Add role helper methods
4. Update user creation forms
5. Add role-specific views

### **Modifying User Creation:**
1. Update registration controllers
2. Modify validation rules
3. Add custom fields
4. Update user model

### **Custom User Lists:**
1. Create new controller methods
2. Add route definitions
3. Create view files
4. Add navigation menu items

## 📱 **API User Management**

### **Registration Endpoints:**
- `POST /api/register` - User registration
- `POST /api/register/step2` - Profile completion
- `POST /api/register/step3` - Final registration

### **User Management Endpoints:**
- `GET /api/users` - List users
- `POST /api/users` - Create user
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user

## 🔄 **User Role Changes**

### **Role Update Process:**
1. Admin changes role in edit form
2. System validates role exists
3. Updates user record
4. Sends notification to user
5. Handles role-specific actions (e.g., instructor approval)

### **Instructor Approval:**
```php
if ($user->role_id != $role->id and $role->name == Role::$teacher) {
    $becomeInstructor = BecomeInstructor::where('user_id', $user->id)
        ->where('status', 'pending')
        ->first();

    if (!empty($becomeInstructor)) {
        $becomeInstructor->update(['status' => 'accept']);
        $becomeInstructor->sendNotificationToUser('accept');
    }
}
```

## 📊 **Database Structure**

### **Users Table:**
```sql
- id (primary key)
- full_name
- email
- mobile
- password
- role_name (string)
- role_id (foreign key to roles table)
- status
- verified
- created_at
- updated_at
```

### **Roles Table:**
```sql
- id (primary key)
- name (unique)
- caption
- is_admin (boolean)
- created_at
```

### **Permissions Table:**
```sql
- id (primary key)
- role_id (foreign key)
- section_id (foreign key)
- allow (boolean)
```

---

**This guide covers all aspects of user management and role assignment in Rocket LMS. Use this as a reference when creating or modifying user-related functionality.** 