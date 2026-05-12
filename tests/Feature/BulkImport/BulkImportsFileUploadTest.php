<?php

namespace Tests\Feature\BulkImport;

use App\Filament\Admin\Pages\BulkImports;
use Filament\Forms\Components\FileUpload;
use Livewire\Livewire;
use Tests\TestCase;

class BulkImportsFileUploadTest extends TestCase
{
    public function test_rubric_template_upload_reinitializes_as_multi_file_upload(): void
    {
        $component = Livewire::test(BulkImports::class);

        $userUpload = $component->instance()->form->getFlatComponents(withHidden: true)['csvPath-users'];

        $this->assertInstanceOf(FileUpload::class, $userUpload);
        $this->assertFalse($userUpload->isMultiple());

        $component->set('type', 'rubric-templates');

        $rubricUpload = $component->instance()->form->getFlatComponents(withHidden: true)['csvPath-rubric-templates'];

        $this->assertInstanceOf(FileUpload::class, $rubricUpload);
        $this->assertTrue($rubricUpload->isMultiple());
        $this->assertTrue($rubricUpload->shouldAppendFiles());
        $this->assertSame(6, $rubricUpload->getMaxParallelUploads());
        $this->assertSame('form.csvPath-rubric-templates', $rubricUpload->getKey());
    }
}
