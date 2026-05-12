<?php

namespace Tests\Unit;

use App\Filament\Admin\Resources\RubricTemplateResource\Pages\ListRubricTemplates;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class RubricTemplateFolderBreadcrumbTest extends TestCase
{
    public function test_folder_breadcrumbs_do_not_override_filament_header_breadcrumbs(): void
    {
        $this->assertTrue(method_exists(ListRubricTemplates::class, 'getFolderBreadcrumbs'));

        $filamentBreadcrumbsMethod = new ReflectionMethod(ListRubricTemplates::class, 'getBreadcrumbs');

        $this->assertNotSame(
            ListRubricTemplates::class,
            $filamentBreadcrumbsMethod->getDeclaringClass()->getName(),
        );
    }
}
