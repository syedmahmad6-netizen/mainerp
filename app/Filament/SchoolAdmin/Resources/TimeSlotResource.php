<?php
namespace App\Filament\SchoolAdmin\Resources;
use App\Filament\SchoolAdmin\Resources\TimeSlotResource\Pages;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class TimeSlotResource extends Resource {
    protected static ?string $model           = TimeSlot::class;
    protected static ?string $navigationIcon  = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Time Slots';
    protected static ?string $navigationGroup = 'Academics';
    protected static ?int    $navigationSort  = 4;
    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Period Name')->placeholder('Period 1, Break, Lunch...')->required()->maxLength(50),
            Forms\Components\TextInput::make('slot_order')->label('Order')->numeric()->required()->minValue(1)->helperText('1 = first period'),
            Forms\Components\TimePicker::make('start_time')->label('Start Time')->required()->seconds(false),
            Forms\Components\TimePicker::make('end_time')->label('End Time')->required()->seconds(false)->after('start_time'),
            Forms\Components\Toggle::make('is_break')->label('Break / Non-Teaching Period')->default(false),
        ])->columns(2);
    }
    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('slot_order')->label('#')->sortable(),
            Tables\Columns\TextColumn::make('name')->label('Period')->sortable()->weight('bold'),
            Tables\Columns\TextColumn::make('start_time')->label('Start')->formatStateUsing(fn($s)=>$s?Carbon::parse($s)->format('h:i A'):'—'),
            Tables\Columns\TextColumn::make('end_time')->label('End')->formatStateUsing(fn($s)=>$s?Carbon::parse($s)->format('h:i A'):'—'),
            Tables\Columns\TextColumn::make('duration')->label('Duration')->getStateUsing(fn(TimeSlot $r)=>Carbon::parse($r->start_time)->diffInMinutes(Carbon::parse($r->end_time)).' min'),
            Tables\Columns\IconColumn::make('is_break')->label('Break')->boolean(),
        ])->actions([Tables\Actions\EditAction::make(),Tables\Actions\DeleteAction::make()])->defaultSort('slot_order');
    }
    public static function getPages(): array {
        return ['index'=>Pages\ListTimeSlots::route('/'),'create'=>Pages\CreateTimeSlot::route('/create'),'edit'=>Pages\EditTimeSlot::route('/{record}/edit')];
    }
}
