<?php

namespace App\Filament\SchoolAdmin\Resources\StudentResource\Pages;

use App\Filament\SchoolAdmin\Resources\StudentResource;
use App\Models\StudentProfile;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    /**
     * Define the read-only student profile display.
     * Uses Filament Infolist — the correct way to show record details in v3.
     */
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            // ── Top: Photo + Key Identity ─────────────────────────────────
            Section::make()
                ->schema([
                    Grid::make(4)->schema([
                        ImageEntry::make('user.profile_photo')
                            ->label('')
                            ->circular()
                            ->defaultImageUrl(fn (StudentProfile $r) =>
                                'https://ui-avatars.com/api/?name=' . urlencode($r->user->name ?? 'S') .
                                '&color=fff&background=059669&size=128'
                            )
                            ->columnSpan(1),

                        Group::make([
                            TextEntry::make('user.name')
                                ->label('Student Name')
                                ->size(TextEntry\TextEntrySize::Large)
                                ->weight(\Filament\Support\Enums\FontWeight::Bold),

                            TextEntry::make('father_name')
                                ->label("Father's Name"),

                            TextEntry::make('admission_number')
                                ->label('Admission No.')
                                ->badge()
                                ->color('primary')
                                ->copyable(),

                            TextEntry::make('status')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'active'      => 'success',
                                    'graduated'   => 'info',
                                    'transferred' => 'warning',
                                    'withdrawn'   => 'danger',
                                    default       => 'gray',
                                }),
                        ])->columnSpan(3),
                    ]),
                ]),

            // ── Academic Information ──────────────────────────────────────
            Section::make('Academic')
                ->columns(3)
                ->schema([
                    TextEntry::make('section.schoolClass.name')
                        ->label('Class'),

                    TextEntry::make('section.name')
                        ->label('Section'),

                    TextEntry::make('section.academicYear.name')
                        ->label('Academic Year'),

                    TextEntry::make('roll_number')
                        ->label('Roll Number')
                        ->default('Not assigned'),

                    TextEntry::make('admission_date')
                        ->label('Admission Date')
                        ->date('d M Y'),

                    TextEntry::make('section.classTeacher.name')
                        ->label('Class Teacher')
                        ->default('Not assigned'),
                ]),

            // ── Personal Details ──────────────────────────────────────────
            Section::make('Personal Details')
                ->columns(3)
                ->schema([
                    TextEntry::make('date_of_birth')
                        ->label('Date of Birth')
                        ->date('d M Y')
                        ->default('—'),

                    TextEntry::make('gender')
                        ->label('Gender')
                        ->formatStateUsing(fn ($state) => ucfirst($state ?? '—')),

                    TextEntry::make('blood_group')
                        ->label('Blood Group')
                        ->badge()
                        ->color('danger')
                        ->default('—'),

                    TextEntry::make('b_form_number')
                        ->label('B-Form Number')
                        ->default('—'),

                    TextEntry::make('city')
                        ->label('City')
                        ->default('—'),

                    TextEntry::make('address')
                        ->label('Address')
                        ->default('—'),
                ]),

            // ── Parents / Contacts ────────────────────────────────────────
            Section::make('Parents & Contacts')
                ->schema([
                    TextEntry::make('parents')
                        ->label('Linked Parents')
                        ->formatStateUsing(function (StudentProfile $record) {
                            $parents = $record->parents()->with('user')->get();
                            if ($parents->isEmpty()) {
                                return 'No parents linked yet. Use "Add Parent" from the student list.';
                            }
                            return $parents->map(fn ($p) =>
                                ($p->user->name ?? 'Unknown') .
                                ' (' . $p->relationship . ')' .
                                ' — ' . ($p->user->phone ?? 'No phone') .
                                ($p->pivot->is_primary_contact ? ' ⭐ Primary' : '')
                            )->join("\n");
                        })
                        ->html(false),
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
        ]);
    }
}
