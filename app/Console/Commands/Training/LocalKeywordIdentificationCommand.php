<?php

namespace App\Console\Commands\Training;

use App\Models\KeywordIndex;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class LocalKeywordIdentificationCommand extends Command
{
    protected $signature = 'training:local-keyword-identification';

    protected $description = 'Command description';

    public function handle(): void
    {
        $training = [];
        $systemPrompt = 'You are a named entity recognition (NER) expert specializing in geographic locations. Your task is to identify whether a supplied line of text contains a reference to a specific physical location, such as a country (UK, US, United Kingdom), county (Leicestershire, London), city (Coventry, Rugby), state (New York, NJ, Alabama), town (Birstall, Lutterworth), etc.\n\nYou will be given a list of keywords separated by commas. Carefully examine each keyword and determine if it explicitly references a specific geographic location. \n\nExample:\n• Input: Plumber Rugby, Builder in Leicester, Leicester Builders, Plastering, Window Fitters, Drain Unblocking, Rugby Boots, Web Design India, Local businesses, Local SEO campaigns, local market optimization\n• Response: [“Plumber Rugby”, “Builder in Leicester”, “Leicester Builders”, “Web Design India”]\n\nImportant Notes:\n1. Exclude general or vague terms that do not clearly reference a specific physical location, like \"local\", \"near me\", \"nearby\", \"neighbourhood\". \n2. Only return keywords that explicitly mention a physical location.\n\nReturn the identified keywords as a JSON array. Do not create, modify, or infer keywords; only return the keywords as supplied.';

        $keywords = KeywordIndex::where('location_reviewed', 1)->inRandomOrder()->cursor();

        foreach ($keywords->chunk(100) as $chunk) {
            $data = [];
            $keywordList = [];
            $localKeywordList = [];

            foreach ($chunk as $keyword) {
                $keywordList[] = $keyword->keyword;

                if ($keyword->location_in_keyword) {
                    $localKeywordList[] = $keyword->keyword;
                }
            }

            $data['messages'][] = [
                "role"    => "system",
                "content" => $systemPrompt,
            ];

            $data['messages'][] = [
                "role"    => "user",
                "content" => implode(', ', $keywordList),
            ];

            $data['messages'][] = [
                "role"    => "assistant",
                "content" => json_encode(['keywords' => $localKeywordList]),
            ];

            $training[] = $data;
        }

        $file = '';

        foreach ($training as $trainingItem) {
            $jsonLine = json_encode($trainingItem);
            $file .= $jsonLine . "\n";
        }

        Storage::disk('s3Training')->put('local/location_identifier_' . date('Y-m-d-H-i-s') . '.jsonl', $file);
    }
}
