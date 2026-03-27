<?php

namespace App\Filament\Admin\Pages;

use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Auth\Pages\Register;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class CoordinatorRegistration extends Register
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('university_id')
                    ->label('University ID')
                    ->required()
                    ->unique(User::class, 'university_id')
                    ->maxLength(255),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    public function getTitle(): string
    {
        return 'Coordinator Registration';
    }

    public function getHeading(): string
    {
        return 'Coordinator Registration';
    }

    protected function handleRegistration(array $data): Model
    {
        /** @var User $user */
        $user = User::create([
            'university_id' => $data['university_id'],
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password'      => $data['password'], // already hashed by dehydrateStateUsing
            'is_approved'   => false,
        ]);

        $user->assignRole('Coordinator');

        return $user;
    }

    /**
     * After registration, do NOT log the user in — just redirect to login
     * with a success notification.
     */
    public function register(): ?RegistrationResponse
    {
        $this->callHook('beforeValidate');
        $data = $this->form->getState();
        $this->callHook('afterValidate');

        $data = $this->mutateFormDataBeforeRegister($data);

        $this->callHook('beforeRegister');
        $user = $this->handleRegistration($data);
        $this->callHook('afterRegister');

        Notification::make()
            ->title('Registration Submitted')
            ->body('Your account is pending approval by a Super Administrator. You can log in once it is activated.')
            ->success()
            ->persistent()
            ->send();

        $this->redirect(filament()->getLoginUrl());

        return null;
    }
}
