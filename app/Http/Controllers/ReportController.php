<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProblemReport;
use App\Events\ProblemReportCreated;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|min:10|max:2000',
                'subject' => 'nullable|string|max:150',
            ], [
                'message.required' => 'Le message est obligatoire.',
                'message.min' => 'Le message doit contenir au moins 10 caractères.',
                'message.max' => 'Le message ne peut pas dépasser 2000 caractères.',
                'subject.max' => 'Le sujet ne peut pas dépasser 150 caractères.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'solutions' => [
                    'Vérifiez que votre message contient au moins 10 caractères',
                    'Si le sujet est trop long, réduisez-le à moins de 150 caractères',
                    'Décrivez clairement le problème rencontré'
                ]
            ], 422);
        }

        try {
            $report = ProblemReport::create([
                'user_id' => optional($request->user())->id,
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => 'open',
            ]);

            try {
                event(new ProblemReportCreated($report));
            } catch (\Throwable $e) {
                \Log::warning('Notification admin non envoyée après signalement: ' . $e->getMessage(), [
                    'report_id' => $report->id,
                    'exception' => $e->getFile() . ':' . $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Signalement envoyé avec succès.']);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi du signalement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'envoyer le signalement pour le moment.',
                'solutions' => [
                    'Vérifiez votre connexion Internet',
                    'Réessayez dans quelques instants',
                    'Si le problème persiste, contactez-nous directement : lomeplus80@gmail.com'
                ]
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $query = ProblemReport::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('status') && in_array($request->status, ['open', 'closed'])) {
            $query->where('status', $request->status);
        }

        $reports = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => ProblemReport::count(),
            'open' => ProblemReport::where('status', 'open')->count(),
            'closed' => ProblemReport::where('status', 'closed')->count(),
        ];

        return view('admin.reports.index', compact('reports', 'stats'));
    }

    public function updateStatus(Request $request, ProblemReport $report)
    {
        try {
            $request->validate([
                'status' => 'required|in:open,closed'
            ], [
                'status.required' => 'Veuillez sélectionner un statut.',
                'status.in' => 'Le statut doit être "ouvert" ou "fermé".',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->with('error_solutions', [
                    'Sélectionnez un statut valide (ouvert ou fermé)',
                    'Vérifiez que le formulaire est correctement rempli'
                ]);
        }

        try {
            $report->status = $request->status;
            $report->save();

            return redirect()->back()->with('success', 'Statut mis à jour.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour du statut: ' . $e->getMessage());
            return back()
                ->with('error', 'Impossible de mettre à jour le statut pour le moment.')
                ->with('error_solutions', [
                    'Réessayez dans quelques instants',
                    'Vérifiez votre connexion Internet'
                ]);
        }
    }
}


