<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Concerns\WorksWithEmployee;
use App\Models\Notice;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class NoticeController extends Controller
{
    use WorksWithEmployee;

    public function index(): View
    {
        $employee = $this->employee();
        $noticesQuery = Notice::query()
            ->with([
                'branch',
                'company',
                'creator:id,name,email',
                'updater:id,name,email',
                'receivers:id,name,email,work_email,avatar,branch_id,department_id',
            ])
            ->where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->whereHas('receivers', fn (Builder $receiverQuery) => $receiverQuery->whereKey($employee->id));

        $allNotices = (clone $noticesQuery)
            ->orderByDesc('notice_publish_date')
            ->orderByDesc('created_at')
            ->get();

        $noticeStats = [
            'total' => $allNotices->count(),
            'published' => $allNotices->filter(fn (Notice $notice): bool => $notice->notice_publish_date !== null)->count(),
            'this_month' => $allNotices->filter(fn (Notice $notice): bool => $notice->notice_publish_date?->isCurrentMonth() ?? false)->count(),
            'receivers' => $allNotices->pluck('receivers')->flatten()->unique('id')->count(),
        ];

        return view('notices.index', [
            'employee' => $employee,
            'noticeStats' => $noticeStats,
            'notices' => $noticesQuery
                ->orderByDesc('notice_publish_date')
                ->orderByDesc('created_at')
                ->paginate(15),
        ]);
    }
}
