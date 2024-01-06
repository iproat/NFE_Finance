<?php

namespace App\Imports;

use App\Lib\Enumerations\UserStatus;
use App\Model\Branch;
use App\Model\Department;
use App\Model\Designation;
use App\Model\Employee;
use App\Model\Role;
use App\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeeImport implements ToModel, WithValidation, WithStartRow, WithLimit
{
    use Importable;

    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function sanitize()
    {
        $this->data['*.19'] = trim($this->data['*.19']);
    }

    public function rules(): array
    {
        return [
            '*.0' => 'required',
            '*.1' => 'required|regex:/^\S*$/u',
            '*.2' => 'required|exists:role,role_name',
            '*.3' => 'required',
            '*.4' => 'required|exists:department,department_name',
            '*.5' => 'required|exists:designation,designation_name',
            '*.6' => 'required|exists:branch,branch_name',
            '*.7' => 'required|exists:user,user_name',
            '*.8' => 'required|exists:user,user_name',
            '*.9' => 'nullable',
            '*.10' => 'nullable|unique:employee,email',
            '*.11' => 'required',
            '*.12' => 'nullable',
            '*.13' => 'nullable',
            '*.14' => 'nullable',
            '*.15' => function ($attribute, $value, $onFailure) {
                $value = trim($value);
                $arr = [null, 'Male', 'Female'];
                if (!in_array($value, $arr)) {
                    $onFailure('Gender is invalid, it should be Male/Female');
                }
            },
            '*.16' => function ($attribute, $value, $onFailure) {
                $value = trim($value);
                $arr = [null, 'Married', 'Unmarried', 'NoDisclosure'];
                if (!in_array($value, $arr)) {
                    $onFailure('Martial Status is invalid, it should be Married/Unmarried/NoDisclosure');
                }
            },
            '*.17' => 'nullable',
            '*.18' => 'nullable',
            '*.19' => function ($attribute, $value, $onFailure) {
                $value = trim($value);
                $arr = [null, 'Muslim', 'Non-Muslim'];
                if (!in_array($value, $arr)) {
                    $onFailure('Religion is invalid, it should be Muslim/Non-Muslim');
                }
            }, '*.20' => function ($attribute, $value, $onFailure) {
                $value = trim($value);
                $arr = [null, 'Omanis', 'Expats'];
                if (!in_array($value, $arr)) {
                    $onFailure('Nationality is invalid, it should be Omanis/Expats');
                }
            },
            '*.31' => function ($attribute, $value, $onFailure) {
                $value = trim($value);
                $arr = [null, 'Active', 'In-Active'];
                if (!in_array($value, $arr)) {
                    $onFailure('Employee Status is invalid, it should be Active/In-Active');
                }
            },
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Sr.No is required',
            '1.required' => 'User name is required',
            '2.required' => 'Role name should be same as the name provided in Master',
            '3.required' => 'FingerPrintId is required (ie: Device Unique id) ',
            '4.required' => 'Department Name should be same as the name provided in Master',
            '5.required' => 'Designation Name should be same as the name provided in Master',
            '6.required' => 'Branch Name should be same as the name provided in Master',
            '7.required' => 'HOD Name should be same as the  user name provided in Master',
            '8.required' => 'Operation Manager Name should be same as the  user name provided in Master',
            // '9.required' => 'Phone No is required',
            // '9.min' => 'Phone No should be min 10 digits',
            // '9.regex' => 'Phone No is invalid',
            '10.nullable' => 'Email is required',
            '11.required' => 'Employee first name is required',
            '12.required' => 'Employee last name is required',
            '13.nullable' => 'Date of birth is required',
            '14.nullable' => 'Date of joining is required',
            '15.in' => 'Invalid Gender ,can user only Male/Female',
            '16.in' => 'Invalid Marital status ,can user only use Married/Unmarried/NoDisclosure',
            '17.nullable' => 'Address is required',
            '18.nullable' => 'Emergency Contact is required',
            '19.in' => 'Invalid status ,can user only use Active/In-Active',

            '1.unique' => 'Username should be unique',
            '1.regex' => 'Space not allowed in Username',
            '2.exists' => 'Role name doest not exists',
            '3.unique' => 'Finger Print should be unique',
            '10.unique' => 'Email ID should be unique',
            '4.exists' => 'Department name doest not exists',
            '5.exists' => 'Designation name doest not exists',
            '7.exists' => 'HOD user name doest not exists',
            '7.exists' => 'Operation Manager user name doest not exists',

        ];
    }

    public function model(array $row)
    {

        $dataUpdate = false;
        $dataInsert = false;
        $usr_status = UserStatus::$ACTIVE;


        $checkEmployee = Employee::where('finger_id', $row[3])->first();

        if ($checkEmployee) {
            $checkUser = User::where('user_id', $checkEmployee->user_id)->first();
            $dataUpdate = true;
        } else {
            $dataInsert = true;
        }

        $dob = "0000-00-00";
        $doj = "0000-00-00";
        $passportExpiryDate = "0000-00-00";
        $visaExpiryDate = "0000-00-00";
        $drivingExpiryDate = "0000-00-00";
        $ResidentCardExpiryDate = "0000-00-00";
        $civilExpiryDate = "0000-00-00";

        if ($row[12]) {
            try {
                $dob = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[12])->format('Y-m-d');
                $password = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[12])->format('Ymd');
            } catch (\Throwable $th) {
                $dob = date('Y-m-d', strtotime($row[13]));
            }
        }

        if ($row[13]) {
            try {
                $doj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[13])->format('Y-m-d');
            } catch (\Throwable $th) {
                $doj = date('Y-m-d', strtotime($row[13]));
            }
        }
        if ($row[22]) {
            try {
                $passportExpiryDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[22])->format('Y-m-d');
            } catch (\Throwable $th) {
                $passportExpiryDate = date('Y-m-d', strtotime($row[22]));
            }
        }
        if ($row[24]) {
            try {
                $visaExpiryDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[24])->format('Y-m-d');
            } catch (\Throwable $th) {
                $visaExpiryDate = date('Y-m-d', strtotime($row[24]));
            }
        }
        if ($row[26]) {
            try {
                $drivingExpiryDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[26])->format('Y-m-d');
            } catch (\Throwable $th) {
                $drivingExpiryDate = date('Y-m-d', strtotime($row[26]));
            }
        }
        if ($row[28]) {
            try {
                $ResidentCardExpiryDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[28])->format('Y-m-d');
            } catch (\Throwable $th) {
                $ResidentCardExpiryDate = date('Y-m-d', strtotime($row[28]));
            }
        }
        if ($row[30]) {
            try {
                $civilExpiryDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[30])->format('Y-m-d');
            } catch (\Throwable $th) {
                $civilExpiryDate = date('Y-m-d', strtotime($row[30]));
            }
        }

        $role = Role::where('role_name', $row[2])->first();
        $dept = Department::where('department_name', $row[4])->first();
        $designation = Designation::where('designation_name', $row[5])->first();

        if (isset($row[7]) && isset($row[8])) {
            $hod = User::where('user_name', $row[7])->first();
            $manager = User::where('user_name', $row[8])->first();
            $emp = Employee::where('user_id', $hod->user_id)->first();
            $manager = Employee::where('user_id', $manager->user_id)->first();
        }

        $branch = Branch::where('branch_name', $row[6])->first();


        if ($row[31] == 'In-Active') {
            $usr_status = UserStatus::$INACTIVE;
        }

        if ($dataInsert) {

            $userData = User::create([
                'user_name' => $row[1],
                'role_id' => $role->role_id,
                'password' => Hash::make('demo1234'),
                'status' => $usr_status,
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
            ]);
            $employeeData = Employee::create([
                'user_id' => $userData->user_id,
                'finger_id' => $row[3],
                'department_id' => $dept->department_id,
                'designation_id' => $designation->designation_id,
                'branch_id' => $branch->branch_id,
                'supervisor_id' => isset($emp->employee_id) ? $emp->employee_id : 1,
                'operation_manager_id' => isset($manager->employee_id) ? $manager->employee_id : 1,
                'phone' => $row[9],
                'email' => $row[10],
                'first_name' => $row[11],
                'last_name' => $row[12],
                'date_of_birth' => $dob,
                'date_of_joining' => $doj,
                'gender' => $row[15] == 'Male' ? 0 : 1,
                'marital_status' => $row[16],
                'address' => $row[17],
                'emergency_contacts' => $row[18],
                'religion' => $row[19] == 'Muslim' ? 0 : 1,
                'nationality' => $row[20] == 'Omanis' ? 0 : 1,
                'document_title8' => $row[21],
                'expiry_date8' => $passportExpiryDate,
                'document_title9' => $row[23],
                'expiry_date9' => $visaExpiryDate,
                'document_title10' => $row[25],
                'expiry_date10' => $drivingExpiryDate,
                'document_title11' => $row[27],
                'expiry_date11' => $ResidentCardExpiryDate,
                'document_title16' => $row[29],
                'expiry_date16' => $civilExpiryDate,
                'status' => $usr_status,
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
            ]);
        }

        if ($dataUpdate) {

            $userData = User::where('user_id', $checkUser->user_id)->update([
                'user_name' => $row[1],
                'role_id' => $role->role_id,
                'status' => $usr_status,
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
            ]);

            $employeeData = Employee::where('employee_id', $checkEmployee->employee_id)->update([
                'user_id' => $checkUser->user_id,
                'finger_id' => $row[3],
                'department_id' => $dept->department_id,
                'designation_id' => $designation->designation_id,
                'branch_id' => $branch->branch_id,
                'supervisor_id' => isset($emp->employee_id) ? $emp->employee_id : 1,
                'operation_manager_id' => isset($manager->employee_id) ? $manager->employee_id : 1,
                'phone' => $row[9],
                'email' => $row[10],
                'first_name' => $row[11],
                'last_name' => $row[12],
                'date_of_birth' => $dob,
                'date_of_joining' => $doj,
                'gender' => $row[15] == 'Male' ? 0 : 1,
                'marital_status' => $row[16],
                'address' => $row[17],
                'emergency_contacts' => $row[18],
                'religion' => $row[19] == 'Muslim' ? 0 : 1,
                'nationality' => $row[20] == 'Omanis' ? 0 : 1,
                'document_title8' => $row[21],
                'expiry_date8' => $passportExpiryDate,
                'document_title9' => $row[23],
                'expiry_date9' => $visaExpiryDate,
                'document_title10' => $row[25],
                'expiry_date10' => $drivingExpiryDate,
                'document_title11' => $row[27],
                'expiry_date11' => $ResidentCardExpiryDate,
                'document_title16' => $row[29],
                'expiry_date16' => $civilExpiryDate,
                'status' => $usr_status,
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,

            ]);
        }
    }

    public function startRow(): int
    {
        return 2;
    }

    public function limit(): int
    {
        return 200;
    }
}
