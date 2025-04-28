<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $group->webinar->title ?? 'N/A' }}</td>
    <td><span class="badge bg-success">{{ $group->members->count() }} students</span></td>
    <td>{{ \Carbon\Carbon::parse($group->meeting_start_time)->format('d M Y - H:i A') }}</td>
    <td>
        <a href="{{ route($screen === 'admin' ? 'course-group.show' : 'course-group.view', $group->id) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-eye"></i>&nbsp;View
        </a>        
    </td>
</tr>