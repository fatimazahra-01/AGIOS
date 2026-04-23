<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use App\Models\Justification;
use Illuminate\Http\Request;
use Gemini\Laravel\Facades\Gemini;

class AIController extends Controller
{
    public function query(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
        ]);

        $query = $request->input('query');

        // Récupérer le contexte (étudiants, absences, justificatifs)
        $students = Student::withCount(['attendances as absences_count' => function ($q) {
            $q->where('status', 'absent');
        }])->get();

        $justificationsPending = Justification::where('status', 'En attente')->count();
        
        // Préparer le contexte pour l'IA
        $context = "Système Scan N Go - Données actuelles :\n";
        $context .= "- Total étudiants : " . $students->count() . "\n";
        $context .= "- Justificatifs en attente : " . $justificationsPending . "\n";
        $context .= "- Liste des étudiants et absences :\n";
        foreach ($students as $student) {
            $abs = $student->absences_count ?? 0;
            $context .= "  - " . $student->name . " (" . $student->class . ") : " . $abs . " absences (Statut : " . $student->status . ")\n";
        }

        try {
            if (!config('gemini.api_key')) {
                throw new \Exception("La clé API Gemini n'est pas configurée dans le fichier .env");
            }

            // Client Gemini avec contournement SSL pour local
            $client = \Gemini::factory()
                ->withApiKey(config('gemini.api_key'))
                ->withHttpClient(new \GuzzleHttp\Client(['verify' => false]))
                ->make();

            // Instructions pour l'IA (System Prompt)
            $systemInstructions = "Tu es l'assistant IA du système Scan N Go.
DIRECTIVES IMPORTANTES :
1. Réponds DIRECTEMENT et CONCISEMENT à la question de l'utilisateur.
2. Si la question est de culture générale ou non liée à Scan N Go, réponds-y simplement sans mentionner les données de présence ou ton rôle d'assistant.
3. N'utilise les données de contexte ci-dessous QUE si l'utilisateur pose une question spécifique sur les étudiants, les absences ou les statistiques du système.
4. Reste professionnel, amical et utilise le français.

CONTEXTE SYSTÈME (À n'utiliser que si pertinent) :
$context";

            $prompt = "$systemInstructions\n\nUtilisateur : $query\nAssistant :";

            $result = $client->generativeModel('gemini-flash-latest')->generateContent($prompt);

            return response()->json([
                'answer' => $result->text(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gemini Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur assistant Gemini: ' . $e->getMessage(),
            ], 500);
        }
    }
}
