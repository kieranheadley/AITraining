<?php

namespace App\Console\Commands\Training;

use App\Models\KeywordIndex;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LocalKeywordIdentificationCommand extends Command
{
    protected $signature = 'training:local-keyword-identification';

    protected $description = 'Command description';

    public function handle(): void
    {
        $training = [];
        $systemPrompt = 'You are an expert in language and are able to identify if a supplied line of text contains a physical location (country, county, city, state, town etc..)\n\nYou will be supplied a list of keywords which have been separated by a comma, you must look carefully through each keyword and determine if that keyword contains a physical geographic location.\n\nYou must review all supplied keywords for the presence of a location. You must return all keywords that contain a location. Only ever return keywords that have been supplied. Never create your own keywords as part of the response.\n\nFor example;\nInput: Plumber Rugby, Builder in Leicester, Leicester Builders, Plastering, Window Fitters, Drain Unblocking, Rugby Boots, Web Design India\nResponse: ["Plumber Rugby", "Builder in Leicester", "Leicester Builders", "Web Design India"]\n\nYour response must be a JSON array in the format [{keyword}, {keyword}]';

        $keywords = KeywordIndex::where('location_reviewed', 1)->cursor();

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

        $filePath = storage_path('training/local/local_keyword_identification.jsonl');

        File::put($filePath, '');

        foreach ($training as $trainingItem) {
            $jsonLine = json_encode($trainingItem);
            File::append($filePath, $jsonLine . "\n");
        }
    }
}
