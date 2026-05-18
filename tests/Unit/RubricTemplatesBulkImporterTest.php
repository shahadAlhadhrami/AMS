<?php

namespace Tests\Unit;

use App\Models\Criterion;
use App\Models\RubricTemplate;
use App\Models\ScoreLevel;
use App\Models\User;
use App\Services\BulkImport\RubricTemplatesBulkImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RubricTemplatesBulkImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_folder_selection_happens_after_validation(): void
    {
        $importer = new RubricTemplatesBulkImporter;

        $this->assertSame([], $importer->extraFormFields());
        $this->assertSame('rubric_folder_id', $importer->contextFormFields()[0]->getName());
    }

    public function test_it_names_imported_rubric_from_original_uploaded_file_name(): void
    {
        $relativePath = 'csv-imports/01KRDHMFDTF545XKWJHFYMYG89.csv';
        $absolutePath = storage_path('app/private/'.$relativePath);

        if (! is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0775, true);
        }

        file_put_contents($absolutePath, implode(PHP_EOL, [
            'deliverable_title,deliverable_max_marks,criterion_title,criterion_description,max_score,is_individual,level_label,level_score,level_description',
            'Presentation,10,Delivery,,10,false,Excellent,10,Clear and confident delivery',
        ]).PHP_EOL);

        try {
            $result = (new RubricTemplatesBulkImporter)->validateRows(
                [$relativePath],
                [],
                ['csvOriginalNames' => [$relativePath => 'Final Presentation Rubric.xlsx']],
            );

            $this->assertFalse($result['hasErrors']);
            $this->assertSame('Final Presentation Rubric', $result['previewData'][0]['file']);
            $this->assertSame('Final Presentation Rubric', $result['previewData'][0]['_parsed']['rubric_name']);
        } finally {
            @unlink($absolutePath);
        }
    }

    public function test_it_fills_down_merged_cell_context_when_importing_levels(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $relativePath = 'csv-imports/rubric-merged-context.csv';
        $absolutePath = storage_path('app/private/'.$relativePath);

        if (! is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0775, true);
        }

        file_put_contents($absolutePath, implode(PHP_EOL, [
            'deliverable_title,deliverable_max_marks,criterion_title,criterion_description,max_score,is_individual,level_label,level_score,level_description',
            'Proposal,4,Scope,,2,false,Excellent,2,Excellent scope',
            ',,,,,,Very Good,1.75,Very good scope',
            ',,,,,,Good,1.5,Good scope',
            ',,Technology,,2,false,Excellent,2,Excellent tech',
            ',,,,,,Very Good,1.75,Very good tech',
            ',,,,,,Good,1.5,Good tech',
        ]).PHP_EOL);

        try {
            $importer = new RubricTemplatesBulkImporter;
            $result = $importer->validateRows([$relativePath], [], []);
            $preview = $result['previewData'][0];

            $this->assertFalse($result['hasErrors']);
            $this->assertSame(1, $preview['deliverables_count']);
            $this->assertSame(2, $preview['criteria_count']);
            $this->assertSame(6, $preview['levels_count']);
            $this->assertSame(4.0, $preview['total_marks']);

            $importer->import($result['previewData'], []);

            $template = RubricTemplate::with('deliverables.criteria.scoreLevels')->first();
            $deliverable = $template->deliverables->first();

            $this->assertSame('4.00', $template->total_marks);
            $this->assertSame('Proposal', $deliverable->title);
            $this->assertSame('4.00', $deliverable->max_marks);
            $this->assertSame(['Scope', 'Technology'], $deliverable->criteria->pluck('title')->all());
            $this->assertSame(6, ScoreLevel::count());
        } finally {
            @unlink($absolutePath);
        }
    }

    public function test_import_skips_per_criterion_total_recalculation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $relativePath = 'csv-imports/rubric-observer-check.csv';
        $absolutePath = storage_path('app/private/'.$relativePath);

        if (! is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0775, true);
        }

        file_put_contents($absolutePath, implode(PHP_EOL, [
            'deliverable_title,deliverable_max_marks,criterion_title,criterion_description,max_score,is_individual,level_label,level_score,level_description',
            'Presentation,10,Delivery,,6,false,Excellent,6,Clear delivery',
            'Presentation,10,Slides,,4,false,Excellent,4,Clear slides',
        ]).PHP_EOL);

        try {
            $importer = new RubricTemplatesBulkImporter;
            $result = $importer->validateRows([$relativePath], [], []);

            DB::enableQueryLog();
            $importer->import($result['previewData'], []);
            $queries = DB::getQueryLog();
            DB::disableQueryLog();

            $this->assertSame(1, RubricTemplate::count());
            $this->assertSame('10.00', RubricTemplate::first()->total_marks);
            $this->assertSame(2, Criterion::count());
            $this->assertSame(2, ScoreLevel::count());
            $this->assertCount(0, array_filter(
                $queries,
                fn (array $query): bool => str_contains($query['query'], 'sum("max_score")'),
            ));
        } finally {
            @unlink($absolutePath);
        }
    }
}
