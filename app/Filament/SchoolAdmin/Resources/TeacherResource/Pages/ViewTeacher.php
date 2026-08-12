<?php

namespace App\Filament\SchoolAdmin\Resources\TeacherResource\Pages;

use App\Filament\SchoolAdmin\Resources\TeacherResource;
use App\Models\TeacherProfile;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewTeacher extends ViewRecord
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            // ── Header: Photo + Key Identity ──────────────────────────────
            Section::make()
                ->schema([
                    Grid::make(4)->schema([
                        ImageEntry::make('user.profile_photo')
                            ->label('')
                            ->circular()
                            ->defaultImageUrl(fn (TeacherProfile $r) =>
                                'https://ui-avatars.com/api/?name=' . urlencode($r->user->name ?? 'T') .
                                '&color=fff&background=7c3aed&size=128'
                            )
                            ->columnSpan(1),

                        Group::make([
                            TextEntry::make('user.name')
                                ->label('Teacher Name')
                                ->size(TextEntry\TextEntrySize::Large)
                                ->weight(\Filament\Support\Enums\FontWeight::Bold),

                            TextEntry::make('employee_id')
                                ->label('Employee ID')
                                ->badge()
                                ->color('primary')
                                ->copyable(),

                            TextEntry::make('status')
                                ->badge()
                                ->color(fn (string $state) => match ($state) {
                                    'active'     => 'success',
                                    'on_leave'   => 'warning',
                                    'resigned'   => 'danger',
                                    'terminated' => 'danger',
                                    default      => 'gray',
                                }),

                            TextEntry::make('employment_type')
                                ->label('Employment Type')
                                ->badge()
                                ->color('info')
                                ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                        ])->columnSpan(3),
                    ]),
                ]),

            // ── Contact & Personal ────────────────────────────────────────
            Section::make('Personal & Contact')
                ->columns(3)
                ->schema([
                    TextEntry::make('user.phone')
                        ->label('Phone')
                        ->copyable(),

                    TextEntry::make('user.email')
                        ->label('Email')
                        ->copyable(),

                    TextEntry::make('cnic')
                        ->label('CNIC')
                        ->copyable(),

                    TextEntry::make('date_of_birth')
                        ->label('Date of Birth')
                        ->date('d M Y')
                        ->placeholder('—'),

                    TextEntry::make('gender')
                        ->label('Gender')
                        ->formatStateUsing(fn ($state) => ucfirst($state ?? '—')),

                    TextEntry::make('joining_date')
                        ->label('Joined On')
                        ->date('d M Y'),
                ]),

            // ── Academic ──────────────────────────────────────────────────
            Section::make('Academic Qualifications')
                ->columns(2)
                ->schema([
                    TextEntry::make('qualification')
                        ->label('Qualification')
                        ->default('—'),

                    TextEntry::make('specialization')
                        ->label('Specialization')
                        ->default('—'),

                    TextEntry::make('subjects')
                        ->label('Assigned Subjects')
                        ->formatStateUsing(fn (TeacherProfile $record) =>
                            $record->subjects->pluck('name')->join(', ') ?: 'No subjects assigned yet.'
                        )
                        ->columnSpanFull(),
                ]),

            // ── Portal Access ─────────────────────────────────────────────
            Section::make('Portal Access')
                ->columns(2)
                ->schema([
                    TextEntry::make('user.email')
                        ->label('Login Email')
                        ->copyable(),

                    TextEntry::make('user.is_active')
                        ->label('Login Status')
                        ->formatStateUsing(fn ($state) => $state ? '✅ Active' : '❌ Inactive'),
                ]),

            // ── Salary (shown collapsed) ──────────────────────────────────
            Section::make('Salary Information')
                ->collapsed()
                ->schema([
                    TextEntry::make('salary')
                        ->label('Monthly Salary')
                        ->money('PKR')
                        ->placeholder('Not set'),
                ]),
        ]);
    }
}
