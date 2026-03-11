<?php

namespace App\Filament\Student\Pages\Auth;

use App\Models\AllowedEmailDomain;
use App\Models\Department;
use App\Models\Specialization;
use Filament\Auth\Pages\Register;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class StudentRegistration extends Register
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getUniversityIdFormComponent(),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getDepartmentFormComponent(),
                $this->getSpecializationFormComponent(),
            ]);
    }

    protected function getUniversityIdFormComponent(): Component
    {
        return TextInput::make('university_id')
            ->label('University ID')
            ->required()
            ->unique('users', 'university_id')
            ->maxLength(255)
            ->autofocus();
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('filament-panels::auth/pages/register.form.name.label'))
            ->required()
            ->maxLength(255);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::auth/pages/register.form.email.label'))
            ->email()
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel())
            ->rules([
                function () {
                    return function (string $attribute, $value, $fail) {
                        if (! AllowedEmailDomain::isEmailAllowed($value)) {
                            $domains = AllowedEmailDomain::getActiveDomains();
                            $domainList = implode(', ', array_map(fn ($d) => '@' . $d, $domains));
                            $fail("Email must be from an allowed university domain ({$domainList}).");
                        }
                    };
                },
            ]);
    }

    protected function getDepartmentFormComponent(): Component
    {
        return Select::make('department_id')
            ->label('Department')
            ->options(Department::pluck('name', 'id'))
            ->searchable()
            ->preload()
            ->required()
            ->live()
            ->afterStateUpdated(fn (callable $set) => $set('specialization_id', null))
            ->dehydrated(false);
    }

    protected function getSpecializationFormComponent(): Component
    {
        return Select::make('specialization_id')
            ->label('Specialization')
            ->options(function (Get $get) {
                $departmentId = $get('department_id');
                if (! $departmentId) {
                    return [];
                }

                return Specialization::where('department_id', $departmentId)
                    ->pluck('name', 'id');
            })
            ->searchable()
            ->preload()
            ->required();
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        unset($data['department_id']);

        return $data;
    }

    protected function handleRegistration(array $data): Model
    {
        $user = $this->getUserModel()::create($data);

        $user->assignRole('Student');

        return $user;
    }
}
