@extends('layouts.admin')

@section('title', 'مدیریت کاربران')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">مدیریت کاربران</h1>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">کارمند جدید</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>نام</th>
                    <th>موبایل</th>
                    <th>نقش</th>
                    <th>وضعیت</th>
                    <th class="text-end">عملیات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td dir="ltr">{{ $user->mobile }}</td>
                        <td><span class="badge bg-secondary">{{ $user->role_label() }}</span></td>
                        <td>
                            @if ($user->is_active)
                                <span class="badge bg-success">فعال</span>
                            @else
                                <span class="badge bg-danger">غیرفعال</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">ویرایش</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4 muted">کاربری ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $users->links() }}
@endsection
