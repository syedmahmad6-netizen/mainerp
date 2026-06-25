<?php

namespace App\Filament\SchoolAdmin\Pages;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SchoolSettings extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'School Settings';
    protected static ?string $navigationGroup = 'Academic Setup';
    protected static ?int    $navigationSort  = 10;
    protected static string  $view            = 'filament.school-admin.pages.school-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $school = tenant()->getSchool();

        $this->form->fill([
            'name'     => $school->name,
            'email'    => $school->email,
            'phone'    => $school->phone,
            'address'  => $school->address,
            'city'     => $school->city,
            'province' => $school->province,
            'logo'     => $school->logo,
            // Pull from settings JSON with safe defaults
            'fee_due_day'      => $school->settings['fee_due_day']     ?? 10,
            'pass_percentage'  => $school->settings['pass_percentage'] ?? 40,
            'grading_system'   => $school->settings['grading_system']  ?? 'percentage',
            'sms_enabled'      => $school->settings['sms_enabled']     ?? false,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('School Identity')
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->label('School Logo')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->directory('school-logos')
                            ->visibility('public')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('name')
                            ->label('School Name')
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->label('School Email')
                            ->email(),

                        Forms\Components\TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel(),
                    ])->columns(2),

                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->label('Street Address')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('city')
                            ->label('City'),

                        Forms\Components\Select::make('province')
                            ->label('Province')
                            ->options([
                                'punjab'           => 'Punjab',
                                'sindh'            => 'Sindh',
                                'kpk'              => 'Khyber Pakhtunkhwa',
                                'balochistan'      => 'Balochistan',
                                'islamabad'        => 'Islamabad Capital Territory',
                                'azad_kashmir'     => 'Azad Kashmir',
                                'gilgit_baltistan' => 'Gilgit-Baltistan',
                            ]),
                    ])->columns(2),

                Forms\Components\Section::make('Academic & Fee Settings')
                    ->description('These settings affect how grades and fees are calculated across the school.')
                    ->schema([
                        Forms\Components\Select::make('grading_system')
                            ->label('Grading System')
                            ->options([
                                'percentage' => 'Percentage (0–100)',
                                'grade'      => 'Letter Grades (A, B, C...)',
                                'gpa'        => 'GPA (4.0 Scale)',
                            ]),

                        Forms\Components\TextInput::make('pass_percentage')
                            ->label('Pass Percentage')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(1)
                            ->maxValue(100),

                        Forms\Components\TextInput::make('fee_due_day')
                            ->label('Monthly Fee Due Day')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(28)
                            ->suffix('th of month'),

                        Forms\Components\Toggle::make('sms_enabled')
                            ->label('SMS Notifications Enabled'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data   = $this->form->getState();
        $school = tenant()->getSchool();

        $school->update([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'],
            'address'  => $data['address'],
            'city'     => $data['city'],
            'province' => $data['province'],
            'logo'     => $data['logo'],
            'settings' => [
                'fee_due_day'     => $data['fee_due_day'],
                'pass_percentage' => $data['pass_percentage'],
                'grading_system'  => $data['grading_system'],
                'sms_enabled'     => $data['sms_enabled'],
            ],
        ]);

        Notification::make()
            ->title('School settings saved successfully.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }
}
