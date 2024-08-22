<?php

namespace App\Console\Commands\Training;

use App\Models\Keywords;
use App\Models\Websites;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SelectNewPageCommand extends Command
{
    protected $signature = 'training:select-new-page';

    protected $description = 'Command description';

    public function handle(): void
    {
        $training = [];
        $systemPrompt = 'Role: \nYou are an expert SEO consultant tasked with identifying the most suitable page for optimising a given keyword based on SEO best practices.\n\nTask Overview:\nYou will be provided with a list of comma-separated keywords. Your task is to:\n\n1. Group Keywords by Surface-Level Similarity:\n - Identify and group keywords based on surface-level similarities to enhance SEO effectiveness.\n - For example, keywords like “builders” and “building” should be grouped together, while “plastering” would be in a separate group. Ensure you still return all keywords which are grouped back as part of the response.\n- Only group very similar keywords, ideally there should only be 3 keywords per page unless the keywords are very similar.\n- You should not group local keywords and non-local keywords on the same page.\n2. Prioritise Location-Based Grouping (When Applicable):\n - If any of the keywords include location information, prioritize grouping them by location.\n - Further, if there are distinctly different services within the same location, create separate groups for each service and location.\n3. Generate SEO-Optimised Page URLs:\n - For each group of keywords, generate a corresponding SEO-optimized page URL that accurately reflects the grouped keywords.\n - Ensure the URL is structured in a way that supports search engine optimisation, considering factors such as keyword relevance and clarity.\n\nResponse:\nYour response must be a JSON array of keywords keyed by your generated page path. For example;\n\nInput: builder leicester, builder rugby, plastering leicester, builders leicester, house builder leicester.\n\nOutput: [ \"/builders-leicester\" => [ \"builder leicester\", \"builders leicester\", \"house builder leicester\" ], \"/builders-rugby\" => [ \"builder rugby\" ], \"/plastering-leicester\" => [ \"plastering leicester\" ]]\n\nImportant! Ensure that all keywords supplied are returned in your response.';

        $websites = Websites::whereIn('id', [18774, 19962, 19800, 21260, 13566, 20189, 18592, 21619, 16437, 1696, 8319, 21237, 19070, 19400, 20257, 18445, 19812, 21024, 20485, 18768, 19172, 20196, 21421, 19191, 10646, 19252, 20423, 18858, 12825, 19474, 16207, 16363, 17271, 15674, 14103, 15765, 8939, 21663, 18624, 20797, 21174, 11943, 16284, 11182, 16135, 17126, 17793, 8931, 11184, 19888, 19574, 21218, 18905, 9772, 19970, 18544, 19839, 16794, 17006, 21129, 15409, 11183, 14317, 15588, 19698, 19468, 20152, 18123, 15678, 17676, 19961, 18711, 21451, 21516, 18990, 12586, 3196, 20559, 16158, 8616, 20102, 9776, 19073, 21743, 14731, 12011, 21163, 18133, 20255, 20051, 16925, 21112, 17045, 6474, 11064, 16520, 11598, 8507, 18243, 19439, 15099, 21573, 18285, 20420, 16177, 9290, 19483, 20736, 19200, 20503, 18611, 15943, 19046, 18268, 20853, 21341, 21319, 20130, 13862, 9566, 18635, 20069, 18758, 8915, 8891, 21358, 7941, 21144, 8972, 21127, 19264, 20606, 20039, 18504, 20546, 20087, 19791, 8934, 1374, 16303, 19273, 21209, 12256, 17060, 18473, 20514, 21507, 20953, 17249, 15748, 21543, 18734, 20876, 15338, 19054, 21026, 17180, 13244, 16365, 8120, 8148, 21481, 14784, 20330, 17918, 20534, 7938])->get();

        foreach ($websites as $website) {
            $data = [];

            $pageKeywords = Keywords::select('hike_assigned_page', 'keyword')
                ->where('website_id', $website->id)
                ->whereNotNull('hike_assigned_page')
                ->get()
                ->groupBy('hike_assigned_page')
                ->map(function ($group) {
                    return $group->pluck('keyword')->toArray();
                })
                ->toArray();

            $data['messages'][] = [
                "role"    => "system",
                "content" => $systemPrompt,
            ];

            $data['messages'][] = [
                "role"    => "user",
                "content" => implode(', ', $website->keywords->whereNotNull('hike_assigned_page')->pluck('keyword')->toArray()),
            ];

            $data['messages'][] = [
                "role"    => "assistant",
                "content" => json_encode($pageKeywords),
            ];

            $training[] = $data;
        }

        $file = '';

        foreach ($training as $trainingItem) {
            $jsonLine = json_encode($trainingItem);
            $file .= $jsonLine . "\n";
        }

        Storage::disk('s3Training')->put('sitemap/assign_new_page_' . date('Y-m-d-H-i-s') . '.jsonl', $file);
    }
}
