<?php
namespace App\Filament\SchoolAdmin\Resources\AssignmentResource\Pages;
use App\Filament\SchoolAdmin\Resources\AssignmentResource;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\StudentProfile;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
class ViewAssignment extends ViewRecord {
    protected static string $resource = AssignmentResource::class;
    protected function getHeaderActions(): array { return [Actions\EditAction::make()]; }
    public function infolist(Infolist $infolist): Infolist {
        return $infolist->schema([
            Section::make('Assignment')->columns(2)->schema([
                TextEntry::make('title')->columnSpanFull()->weight(\Filament\Support\Enums\FontWeight::Bold)->size(TextEntry\TextEntrySize::Large),
                TextEntry::make('subject.name')->label('Subject')->badge()->color('info'),
                TextEntry::make('section.schoolClass.name')->label('Class')->badge()->color('primary'),
                TextEntry::make('teacher.name')->label('Assigned By'),
                TextEntry::make('due_date')->label('Due Date')->date('d M Y'),
                TextEntry::make('status')->badge()->color(fn($state)=>$state==='active'?'success':'gray'),
                TextEntry::make('allow_submission')->label('Online Submission')
                    ->formatStateUsing(fn($state)=>$state?'✅ Enabled':'❌ Disabled'),
                TextEntry::make('description')->label('Instructions')->columnSpanFull()->default('No instructions provided.'),
            ]),
            Section::make('Submissions')->schema([
                TextEntry::make('submissions_count')->label('Total Submitted')
                    ->getStateUsing(fn(Assignment $r)=>$r->submissions()->count().' of '.StudentProfile::where('current_section_id',$r->section_id)->count().' students'),
            ]),
        ]);
    }
}