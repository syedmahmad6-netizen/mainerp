<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\SchoolResource\Pages;
use App\Models\School;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SchoolResource extends Resource
{
    protected static ?string $model           = School::class;
    protected static ?string $navigationIcon  = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Schools';
    protected static ?string $navigationGroup = 'Platform Management';
    protected static ?int    $navigationSort  = 1;

    // Live count badge on sidebar nav item
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    // ─────────────────────────────────────────────────────────────────────
    // FORM
    // ─────────────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('School Identity')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('School Name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                            // Auto-generate subdomain only on create, not edit
                            if ($operation !== 'create') {
                                return;
                            }
                            $set('subdomain', Str::slug($state));
                        }),

                    Forms\Components\TextInput::make('subdomain')
                        ->label('Subdomain')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->prefix('https://')
                        ->suffix('.gnosis.ac.pk')
                        ->helperText('Lowercase letters, numbers, hyphens only. Cannot be changed easily after setup.')
                        ->rules(['alpha_dash', 'lowercase']),

                    Forms\Components\TextInput::make('email')
                        ->label('School Email')
                        ->email()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->label('Phone Number')
                        ->tel()
                        ->maxLength(20),
                ])->columns(2),

            Forms\Components\Section::make('Location')
                ->schema([
                    Forms\Components\TextInput::make('address')
                        ->label('Street Address')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('city')
                        ->label('City')
                        ->maxLength(100),

                    Forms\Components\Select::make('province')
                        ->label('Province / Territory')
                        ->options([
                            'punjab'           => 'Punjab',
                            'sindh'            => 'Sindh',
                            'kpk'              => 'Khyber Pakhtunkhwa',
                            'balochistan'      => 'Balochistan',
                            'azad_kashmir'     => 'Azad Kashmir',
                            'gilgit_baltistan' => 'Gilgit-Baltistan',
                            'islamabad'        => 'Islamabad Capital Territory',
                        ]),
                ])->columns(2),

            Forms\Components\Section::make('Subscription')
                ->schema([
                    Forms\Components\Select::make('plan')
                        ->label('Plan')
                        ->options([
                            'trial'   => '🔔 Trial',
                            'basic'   => '⭐ Basic',
                            'premium' => '💎 Premium',
                        ])
                        ->default('trial')
                        ->required()
                        ->live(),

                    Forms\Components\DatePicker::make('trial_ends_at')
                        ->label('Trial Expiry Date')
                        ->minDate(now())
                        ->visible(fn (Forms\Get $get) => $get('plan') === 'trial'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Portal Active')
                        ->default(true)
                        ->helperText('Deactivating prevents school staff from logging in.'),
                ])->columns(3),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // TABLE
    // ─────────────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(
                        fn (School $record) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($record->name) .
                        '&color=fff&background=4f46e5&size=64'
                    ),

                Tables\Columns\TextColumn::make('name')
                    ->label('School')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('subdomain')
                    ->label('Portal URL')
                    ->formatStateUsing(fn ($state) => $state . '.gnosis.ac.pk')
                    ->copyable()
                    ->copyMessage('URL copied')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('city')
                    ->label('City')
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'trial'   => 'warning',
                        'basic'   => 'info',
                        'premium' => 'success',
                        default   => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Users')
                    ->counts('users')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('plan')
                    ->options([
                        'trial'   => 'Trial',
                        'basic'   => 'Basic',
                        'premium' => 'Premium',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Portal Status')
                    ->placeholder('All')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
            ])

            ->actions([

                Tables\Actions\EditAction::make(),

                // ── Open portal in new tab ───────────────────────────────
                Tables\Actions\Action::make('visit_portal')
                    ->label('Open Portal')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (School $record) =>
                        'https://' . $record->subdomain . '.gnosis.ac.pk/admin'
                    )
                    ->openUrlInNewTab(),

                // ── Setup Principal ──────────────────────────────────────
                // Only visible when no principal account exists yet
                Tables\Actions\Action::make('setup_principal')
                    ->label('Setup Principal')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->visible(fn (School $record) =>
                        ! User::withoutGlobalScopes()
                            ->where('school_id', $record->id)
                            ->where('role', 'principal')
                            ->exists()
                    )
                    ->form([
                        Forms\Components\TextInput::make('principal_name')
                            ->label('Full Name')->required(),
                        Forms\Components\TextInput::make('principal_email')
                            ->label('Email Address')->email()
                            ->unique('users', 'email')->required(),
                        Forms\Components\TextInput::make('principal_phone')
                            ->label('Phone Number')->tel(),
                        Forms\Components\TextInput::make('temp_password')
                            ->label('Temporary Password')
                            ->default(fn () => Str::random(10))
                            ->required()
                            ->helperText('⚠️ Note this down — share it with the principal.'),
                    ])
                    ->modalHeading(fn (School $record) => 'Create Principal for ' . $record->name)
                    ->action(function (School $record, array $data): void {
                        User::withoutGlobalScopes()->create([
                            'school_id' => $record->id,
                            'name'      => $data['principal_name'],
                            'email'     => $data['principal_email'],
                            'phone'     => $data['principal_phone'] ?? null,
                            'password'  => $data['temp_password'],
                            'role'      => 'principal',
                            'is_active' => true,
                        ]);
                        Notification::make()
                            ->title('Principal account created!')
                            ->body("Email: {$data['principal_email']} | Password: {$data['temp_password']}")
                            ->success()->persistent()->send();
                    }),

                // ── Create School Manager ────────────────────────────────
                Tables\Actions\Action::make('create_manager')
                    ->label('Create Manager')
                    ->icon('heroicon-o-briefcase')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('manager_name')
                            ->label('Full Name')->required(),
                        Forms\Components\TextInput::make('manager_email')
                            ->label('Email Address')->email()
                            ->unique('users', 'email')->required(),
                        Forms\Components\TextInput::make('manager_phone')
                            ->label('Phone Number')->tel(),
                        Forms\Components\TextInput::make('temp_password')
                            ->label('Temporary Password')
                            ->default(fn () => Str::random(10))
                            ->required()
                            ->helperText('⚠️ Note this down — share it with the school manager.'),
                    ])
                    ->modalHeading(fn (School $record) => 'Create Manager for ' . $record->name)
                    ->action(function (School $record, array $data): void {
                        User::withoutGlobalScopes()->create([
                            'school_id' => $record->id,
                            'name'      => $data['manager_name'],
                            'email'     => $data['manager_email'],
                            'phone'     => $data['manager_phone'] ?? null,
                            'password'  => $data['temp_password'],
                            'role'      => 'school_manager',
                            'is_active' => true,
                        ]);
                        Notification::make()
                            ->title('School Manager account created!')
                            ->body("Email: {$data['manager_email']} | Password: {$data['temp_password']}")
                            ->success()->persistent()->send();
                    }),

                // ── Access School Panel (Impersonation) ──────────────────
                Tables\Actions\Action::make('access_school_panel')
                    ->label('Access Panel')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(fn (School $record) => 'Enter ' . $record->name . '\'s Panel?')
                    ->modalDescription('You will be logged into this school\'s admin panel. An impersonation banner will show. You can return here anytime.')
                    ->modalSubmitActionLabel('Enter School Panel')
                    ->url(fn (School $record) => route('super-admin.impersonate', $record))
                    ->visible(fn (School $record) =>
                        User::withoutGlobalScopes()
                            ->where('school_id', $record->id)
                            ->whereIn('role', ['principal', 'school_manager'])
                            ->exists()
                    ),

                // ── Quick toggle active/inactive ─────────────────────────
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (School $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (School $record) => $record->is_active
                        ? 'heroicon-o-x-circle'
                        : 'heroicon-o-check-circle'
                    )
                    ->color(fn (School $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (School $record) => $record->update(['is_active' => ! $record->is_active])),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('deactivate_selected')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])

            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSchools::route('/'),
            'create' => Pages\CreateSchool::route('/create'),
            'edit'   => Pages\EditSchool::route('/{record}/edit'),
        ];
    }
}
