<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ConceptController extends Controller
{
    public function validateConcept(Request $request)
    {
        $conceptId = $request->input('concept_id');

        try {
            DB::table('pesquisas')
                ->where('id', $conceptId)
                ->update(['concept_validated_at' => now()]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function editConcept(Request $request)
    {
        $conceptId = $request->input('concept_id');
        $newText = $request->input('new_text');

        try {
            DB::table('pesquisas')
                ->where('id', $conceptId)
                ->update(['concept' => $newText]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function removeConcept(Request $request)
    {
        $conceptId = $request->input('concept_id');

        try {
            //concept and concept_validated_at values = null
            DB::table('pesquisas')
                ->where('id', $conceptId)
                ->update(['concept' => null, 'concept_validated_at' => null]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    //function to save the concept in the database
    public function saveConcept(Request $request)
    {
        $concept = $request->input('concept');
        $conceptId = $request->input('concept_id');

        try {
            DB::table('pesquisas')
                ->where('id', $conceptId)
                ->update(['concept' => $concept]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function generateConcept(Request $request)
    {
        $messages = $request->input('messages');
        if (!is_array($messages) || empty($messages)) {
            return response()->json(['success' => false, 'message' => 'Mensagens de prompt ausentes. Por favor, resolva isso e tente novamente.']);
        }

        $model = $request->input('model');

        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            return response()->json(['success' => false, 'message' => 'Chave de API ausente. Por favor, resolva isso e tente novamente.']);
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey
            ])->post('https://api.openai.com/v1/chat/completions', [
                        'model' => $model,
                        'messages' => $messages,
                        'max_tokens' => 4000,
                        'n' => 1,
                        'stop' => null,
                        'temperature' => 0.3
                    ]);


            $responseData = $response->json();
            if (is_array($responseData['choices']) && !empty($responseData['choices'][0]['message']) && !empty($responseData['choices'][0]['message']['content'])) {
                $concept = $responseData['choices'][0]['message']['content'];
            } else {
                $responseData = json_encode($responseData);
                throw new \Exception('Erro ao gerar conceito. Por favor, tente novamente.\n\n' + $responseData);
            }
            return response()->json(['success' => true, 'concept' => trim($concept)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
