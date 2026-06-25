<?php
namespace App\Filament\SchoolAdmin\Resources;
use App\Filament\SchoolAdmin\Resources\AssignmentResource\Pages;
use App\Models\Assignment;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class AssignmentResource extends Resource {
    protected static ?string $model           = Assignment::class;
    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Homework';
    protected static ?string $navigationGroup = 'Academics';
    protected static ?int    $navigationSort  = 12;
    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make('Assignment Details')->schema([
                Forms\Components\TextInput::make('title')->label('Title')->required()->maxLength(200)->columnSpanFull(),
                Forms\Components\Select::make('section_id')->label('Class & Section')
                    ->options(fn()=>Section::with('schoolClass')->get()->mapWithKeys(fn($s)=>[$s->id=>$s->schoolClass->name.' – '.$s->name]))
                    ->required()->searchable(),
                Forms\Components\Select::make('subject_id')->label('Subject')
                    ->options(fn()=>Subject::all()->pluck('name','id'))->required()->searchable(),
                Forms\Components\Select::make('teacher_id')->label('Assigned By')
                    ->options(fn()=>User::where('role','teacher')->pluck('name','id'))
                    ->default(fn()=>auth()->id())->required(),
                Forms\Components\DatePicker::make('due_date')->label('Due Date')->required()->minDate(today()),
                Forms\Components\Select::make('status')->options(['active'=>'Active','closed'=>'Closed'])->default('active')->required(),
                Forms\Components\Textarea::make('description')->label('Instructions')->rows(3)->columnSpanFull(),
                Forms\Components\FileUpload::make('attachment')->label('Attachment (optional)')->directory('assignments')
                    ->acceptedFileTypes(['application/pdf','image/jpeg','image/png'])->maxSize(2048)->columnSpanFull(),
                Forms\Components\Toggle::make('allow_submission')->label('Allow students to submit online')->default(true)
                    ->helperText('Students can upload their completed work from the portal.'),
            ])->columns(2),
        ]);
    }
    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->label('Assignment')->sortable()->searchable()->limit(40)->weight('bold'),
            Tables\Columns\TextColumn::make('subject.name')->label('Subject')->badge()->color('info'),
            Tables\Columns\TextColumn::make('section.schoolClass.name')->label('Class')->badge()->color('primary'),
            Tables\Columns\TextColumn::make('section.name')->label('Section')->badge()->color('gray'),
            Tables\Columns\TextColumn::make('teacher.name')->label('Teacher')->toggleable(),
            Tables\Columns\TextColumn::make('due_date')->label('Due Date')->date('d M Y')->sortable()
                ->color(fn(Assignment $r)=>$r->isOverdue()?'danger':''),
            Tables\Columns\TextColumn::make('submissions_count')->label('Submissions')->counts('submissions')->badge()->color('success'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn($s)=>$s==='active'?'success':'gray'),
        ])->filters([
            Tables\Filters\SelectFilter::make('section_id')->label('Section')
                ->options(fn()=>Section::with('schoolClass')->get()->mapWithKeys(fn($s)=>[$s->id=>$s->schoolClass->name.' – '.$s->name])),
            Tables\Filters\SelectFilter::make('status')->options(['active'=>'Active','closed'=>'Closed']),
        ])->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('close')->label('Close')->icon('heroicon-o-lock-closed')->color('warning')
                ->requiresConfirmation()->visible(fn(Assignment $r)=>$r->status==='active')
                ->action(fn(Assignment $r)=>$r->update(['status'=>'closed'])),
        ])->defaultSort('due_date','asc');
    }
    public static function getPages(): array {
        return ['index'=>Pages\ListAssignments::route('/'),'create'=>Pages\CreateAssignment::route('/create'),'view'=>Pages\ViewAssignment::route('/{record}'),'edit'=>Pages\EditAssignment::route('/{record}/edit')];
    }
}