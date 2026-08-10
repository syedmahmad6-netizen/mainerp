<?php

namespace App\Filament\SchoolAdmin\Resources;

use App\Filament\SchoolAdmin\Resources\ParentResource\Pages;
use App\Models\ParentProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ParentResource extends Resource
{
    protected static ?string $model           = ParentProfile::class;
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Parents';
    protected static ?string $navigationGroup = 'People';
    protected static ?int    $navigationSort  = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Parent Information')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Full Name *')
                        ->required()
                        ->maxLength(100),

                    Forms\Components\Select::make('relationship')
                        ->label('Relationship to Student *')
                        ->options([
                            'Father'   => 'Father',
                            'Mother'   => 'Mother',
                            'Guardian' => 'Guardian',
                            'Brother'  => 'Brother',
                            'Sister'   => 'Sister',
                            'Other'    => 'Other',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('phone')
                        ->label('Phone Number *')
                        ->tel()
                        ->required(),

                    Forms\Components\TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->unique('users', 'email')
                        ->helperText('Required for parent portal login. Optional if parent does not need login.'),

                    Forms\Components\TextInput::make('cnic')
                        ->label('CNIC')
                        ->placeholder('00000-0000000-0')
                        ->maxLength(15),

                    Forms\Components\TextInput::make('occupation')
                        ->label('Occupation')
                        ->maxLength(100),
                ])->columns(2),

            Forms\Components\Section::make('Portal Access')
                ->schema([
                    Forms\Components\Placeholder::make('portal_note')
                        ->label('')
                        ->content('If an email is provided above, the parent will receive portal login credentials automatically. They can view their child\'s attendance, results, and fee status.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Parent Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('relationship')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('Children')
                    ->counts('students')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('user.is_active')
                    ->label('Portal Login')
                    ->boolean(),

                Tables\Columns\TextColumn::make('cnic')
                    ->label('CNIC')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('relationship')
                    ->options([
                        'Father'   => 'Father',
                        'Mother'   => 'Mother',
                        'Guardian' => 'Guardian',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListParents::route('/'),
            'create' => Pages\CreateParent::route('/create'),
            'edit'   => Pages\EditParent::route('/{record}/edit'),
        ];
    }
}
