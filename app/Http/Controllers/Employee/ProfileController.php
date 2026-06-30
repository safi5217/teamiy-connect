<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Concerns\WorksWithEmployee;
use App\Http\Requests\Employee\UpdateProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    use WorksWithEmployee;

    public function show(): View
    {
        return view('profile.index', [
            'employee' => $this->employee()->load([
                'company',
                'branch',
                'department',
                'post',
                'officeTime',
                'supervisor',
                'employeeAccount',
                'employeeSalary',
            ]),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $employee = $this->employee();
        $validated = $request->validated();
        $profileData = Arr::except($validated, ['avatar']);

        if ($request->hasFile('avatar')) {
            $oldAvatar = (string) $employee->avatar;
            $extension = $request->file('avatar')->getClientOriginalExtension();
            $fileName = 'employee-'.$employee->id.'-'.Str::uuid().'.'.$extension;

            Storage::disk('public')->makeDirectory('profile-avatars');

            $profileData['avatar'] = $request->file('avatar')->storeAs('profile-avatars', $fileName, 'public');

            if (str_starts_with($oldAvatar, 'profile-avatars/')) {
                Storage::disk('public')->delete($oldAvatar);
            }
        }

        $employee->forceFill($profileData)->save();

        return back()->with('status', 'Profile updated successfully.');
    }
}
