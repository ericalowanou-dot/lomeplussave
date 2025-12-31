<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProblemReport;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|min:10',
                'subject' => 'nullable|string|max:150',
            ], [
                'message.required' => 'Le message est obligatoire.',
                'message.min' => 'Le message doit contenir au moins 10 caractères pour être clair.',
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
            ProblemReport::create([
                'user_id' => optional($request->user())->id,
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => 'open',
            ]);

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

    public function index()
    {
        $reports = ProblemReport::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.reports.index', compact('reports'));
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


