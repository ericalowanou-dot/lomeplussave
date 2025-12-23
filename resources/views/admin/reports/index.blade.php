@extends('admin.layout')

@section('title', 'Signalements')
@section('page-title', 'Signalements des utilisateurs')

@section('content')
<div class="admin-card">
    <div class="admin-card-header d-flex justify-content-between align-items-center">
        <h5 class="admin-card-title"><i class="fas fa-flag text-danger"></i> Signalements ({{ $reports->total() }})</h5>
    </div>
    <div class="admin-card-body">
        @if($reports->count())
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Utilisateur</th>
                        <th>Sujet</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td>{{ $report->id }}</td>
                        <td>
                            @if($report->user)
                                <a href="{{ route('admin.users.show', $report->user) }}">{{ $report->user->name }}</a>
                                <div><small class="text-muted">{{ $report->user->email }}</small></div>
                            @else
                                <span class="text-muted">Anonyme</span>
                            @endif
                        </td>
                        <td>{{ $report->subject ?: '—' }}</td>
                        <td style="max-width: 420px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $report->message }}">{{ $report->message }}</td>
                        <td>{{ $report->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($report->status === 'open')
                                <span class="badge bg-warning text-dark">Ouvert</span>
                            @else
                                <span class="badge bg-success">Fermé</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.reports.update-status', $report) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="{{ $report->status === 'open' ? 'closed' : 'open' }}">
                                <button class="btn btn-sm {{ $report->status === 'open' ? 'btn-success' : 'btn-warning' }}">
                                    {{ $report->status === 'open' ? 'Marquer comme fermé' : 'Réouvrir' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper">{{ $reports->links() }}</div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-flag fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun signalement</h5>
            </div>
        @endif
    </div>
</div>
@endsection


