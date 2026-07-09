<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function loginless(Request $request)
    {
        try {
            $request->validate([
                'employee_no' => 'required|string|max:50',
                'password' => 'required|string|size:8',
            ]);
        } catch (ValidationException $e) {
            return response(['status' => 422, 'message' => 'Data tidak valid.', 'type' => 'fail', 'errors' => $e->errors()], 422);
        }

        try {
            $birthDate = Carbon::createFromFormat('dmY', $request->password)->format('Y-m-d');
        } catch (\Throwable $th) {
            return response(['status' => 401, 'message' => 'Username atau password salah.', 'type' => 'fail'], 401);
        }

        try {
            $employee = Employee::where('employee_no', $request->employee_no)
                ->where('birth_date', $birthDate)
                ->where('fk_status_id', 6)
                ->first();

            if ($employee === null) {
                return response(['status' => 401, 'message' => 'Username atau password salah.', 'type' => 'fail'], 401);
            }

            $employee->tokens()->delete();
            $token = $employee->createToken('mobile-app', ['*'], now()->addDays(30))->plainTextToken;

            return response([
                'status' => 200,
                'message' => 'Login Success!',
                'type' => 'success',
                'token' => $token,
                'data' => [
                    'employee_no' => $employee->employee_no,
                    'full_name' => $employee->full_name,
                    'position' => $employee->position,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response(['status' => 500, 'message' => 'Something went wrong.', 'type' => 'fail'], 500);
        }
    }
}
