<?php

namespace App\Filament\SchoolAdmin\Resources;

use App\Filament\SchoolAdmin\Resources\FeeCollectionResource\Pages;
use App\Models\AcademicYear;
use App\Models\FeeCollection;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudentProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FeeCollectionResource extends Resource
{
    protected static ?string $model           = FeeCollection::class;
    protected static ?string $navigationIcon  = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Fee Collection';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int    $navigationSort  = 3;

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('status', 'pending')->count()
            + static::getModel()::where('status', 'overdue')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Fee Record')
                ->schema([
                    Forms\Components\Select::make('student_profile_id')
                        ->label('Student')
                        ->options(fn () =>
                            StudentProfile::with('user')
                                ->get()
                                ->mapWithKeys(fn ($s) => [
                                    $s->id => ($s->admission_number ?? '') . ' — ' . ($s->user->name ?? '')
                                ])
                        )
                        ->searchable()
                        ->required()
                        ->live(),

                    Forms\Components\Select::make('fee_type_id')
                        ->label('Fee Type')
                        ->options(fn () => FeeType::where('is_active', true)->pluck('name', 'id'))
                        ->required(),

                    Forms\Components\Select::make('academic_year_id')
                        ->label('Academic Year')
                        ->options(fn () => AcademicYear::all()->pluck('name', 'id'))
                        ->default(fn () => AcademicYear::where('is_current', true)->value('id'))
                        ->required(),

                    Forms\Components\DatePicker::make('fee_month')
                        ->label('Fee Month')
                        ->displayFormat('F Y')
                        ->default(today()->startOfMonth())
                        ->required(),

                    Forms\Components\TextInput::make('amount_due')
                        ->label('Amount Due (PKR)')
                        ->numeric()
                        ->prefix('Rs.')
                        ->required(),

                    Forms\Components\TextInput::make('discount')
                        ->label('Discount (PKR)')
                        ->numeric()
                        ->prefix('Rs.')
                        ->default(0),

                    Forms\Components\Select::make('status')
                        ->options([
                            'pending'  => 'Pending',
                            'paid'     => 'Paid',
                            'partial'  => 'Partial',
                            'overdue'  => 'Overdue',
                            'waived'   => 'Waived',
                        ])
                        ->default('pending')
                        ->required()
                        ->live(),

                    Forms\Components\TextInput::make('amount_paid')
                        ->label('Amount Paid (PKR)')
                        ->numeric()
                        ->prefix('Rs.')
                        ->default(0)
                        ->visible(fn (Forms\Get $get) =>
                            in_array($get('status'), ['paid', 'partial'])
                        ),

                    Forms\Components\Select::make('payment_method')
                        ->label('Payment Method')
                        ->options([
                            'cash'          => 'Cash',
                            'cheque'        => 'Cheque',
                            'bank_transfer' => 'Bank Transfer',
                            'jazzcash'      => 'JazzCash',
                            'easypaisa'     => 'EasyPaisa',
                        ])
                        ->visible(fn (Forms\Get $get) =>
                            in_array($get('status'), ['paid', 'partial'])
                        ),

                    Forms\Components\DatePicker::make('paid_date')
                        ->label('Payment Date')
                        ->default(today())
                        ->visible(fn (Forms\Get $get) =>
                            in_array($get('status'), ['paid', 'partial'])
                        ),

                    Forms\Components\Textarea::make('remarks')
                        ->label('Remarks')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('student.admission_number')
                    ->label('Adm. No.')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('student.section.schoolClass.name')
                    ->label('Class')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('feeType.name')
                    ->label('Fee Type')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('fee_month')
                    ->label('Month')
                    ->formatStateUsing(fn ($state) => $state?->format('M Y'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_due')
                    ->label('Due')
                    ->money('PKR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount')
                    ->label('Discount')
                    ->money('PKR')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Paid')
                    ->money('PKR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('balance')
                    ->label('Balance')
                    ->money('PKR')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'paid'    => 'success',
                        'partial' => 'warning',
                        'pending' => 'info',
                        'overdue' => 'danger',
                        'waived'  => 'gray',
                        default   => 'gray',
                    }),

                Tables\Columns\TextColumn::make('receipt_no')
                    ->label('Receipt')
                    ->copyable()
                    ->default('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('paid_date')
                    ->label('Paid On')
                    ->date('d M Y')
                    ->default('—')
                    ->toggleable(),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid'    => 'Paid',
                        'partial' => 'Partial',
                        'overdue' => 'Overdue',
                        'waived'  => 'Waived',
                    ]),

                Tables\Filters\SelectFilter::make('fee_type_id')
                    ->label('Fee Type')
                    ->options(fn () => FeeType::all()->pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(fn () => AcademicYear::all()->pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('class')
                    ->label('Class')
                    ->options(fn () =>
                        SchoolClass::orderBy('numeric_order')->get()->pluck('name', 'id')
                    )
                    ->query(fn (Builder $q, array $data) =>
                        $q->when($data['value'], fn ($q, $v) =>
                            $q->whereHas('student.section', fn ($s) =>
                                $s->where('school_class_id', $v)
                            )
                        )
                    ),
            ])

            ->headerActions([
                Tables\Actions\Action::make('generate_monthly_fees')
                    ->label('Generate Monthly Fees')
                    ->icon('heroicon-o-calendar')
                    ->color('primary')
                    ->form([
                        Forms\Components\DatePicker::make('fee_month')
                            ->label('Select Month')
                            ->displayFormat('F Y')
                            ->default(today()->startOfMonth())
                            ->required(),

                        Forms\Components\Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->options(fn () => AcademicYear::all()->pluck('name', 'id'))
                            ->default(fn () => AcademicYear::where('is_current', true)->value('id'))
                            ->required(),

                        Forms\Components\Select::make('school_class_id')
                            ->label('Generate For Class')
                            ->options(fn () =>
                                collect(['all' => 'All Classes'])
                                    ->merge(
                                        SchoolClass::orderBy('numeric_order')
                                            ->get()
                                            ->pluck('name', 'id')
                                    )
                            )
                            ->default('all'),
                    ])
                    ->action(function (array $data): void {
                        $feeMonth       = \Carbon\Carbon::parse($data['fee_month'])->startOfMonth();
                        $academicYearId = $data['academic_year_id'];
                        $classFilter    = $data['school_class_id'] !== 'all'
                            ? $data['school_class_id']
                            : null;

                        $generated = 0;
                        $skipped   = 0;

                        $structures = FeeStructure::where('academic_year_id', $academicYearId)
                            ->where('is_active', true)
                            ->where('frequency', 'monthly')
                            ->when($classFilter, fn ($q) => $q->where('school_class_id', $classFilter))
                            ->with('feeType')
                            ->get();

                        $students = StudentProfile::where('status', 'active')
                            ->with(['section.schoolClass', 'user'])
                            ->get();

                        DB::transaction(function () use (
                            $students, $structures, $feeMonth, $academicYearId,
                            $classFilter, &$generated, &$skipped
                        ) {
                            foreach ($students as $student) {
                                $classId = $student->section?->school_class_id;
                                if (! $classId) continue;
                                if ($classFilter && $classId !== (int) $classFilter) continue;

                                $applicable = $structures->where('school_class_id', $classId);

                                foreach ($applicable as $structure) {
                                    $exists = FeeCollection::where('student_profile_id', $student->id)
                                        ->where('fee_type_id', $structure->fee_type_id)
                                        ->whereDate('fee_month', $feeMonth)
                                        ->exists();

                                    if ($exists) {
                                        $skipped++;
                                        continue;
                                    }

                                    $assignment = \App\Models\StudentFeeAssignment::where('student_profile_id', $student->id)
                                        ->where('fee_structure_id', $structure->id)
                                        ->where('is_active', true)
                                        ->first();

                                    $discount = 0;
                                    if ($assignment) {
                                        $discount = match ($assignment->discount_type) {
                                            'percentage' => $structure->amount * $assignment->discount_value / 100,
                                            'fixed'      => $assignment->discount_value,
                                            default      => 0,
                                        };
                                    }

                                    $amountDue = $structure->amount;
                                    $balance   = $amountDue - $discount;

                                    FeeCollection::create([
                                        'school_id'          => tenant()->getSchoolId(),
                                        'student_profile_id' => $student->id,
                                        'fee_type_id'        => $structure->fee_type_id,
                                        'fee_structure_id'   => $structure->id,
                                        'academic_year_id'   => $academicYearId,
                                        'fee_month'          => $feeMonth,
                                        'amount_due'         => $amountDue,
                                        'discount'           => $discount,
                                        'fine'               => 0,
                                        'amount_paid'        => 0,
                                        'balance'            => $balance,
                                        'status'             => 'pending',
                                    ]);

                                    $generated++;
                                }
                            }
                        });

                        \Filament\Notifications\Notification::make()
                            ->title('Monthly fees generated!')
                            ->body("{$generated} fee records created. {$skipped} already existed.")
                            ->success()
                            ->send();
                    }),
            ])

            ->actions([
                Tables\Actions\Action::make('collect_payment')
                    ->label('Collect')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->visible(fn (FeeCollection $r) => $r->isPending())
                    ->form(function (FeeCollection $record) {
                        return [
                            Forms\Components\Placeholder::make('summary')
                                ->label('Fee Summary')
                                ->content(
                                    "Student: {$record->student->user->name}\n" .
                                    "Fee: {$record->feeType->name} — {$record->fee_month_label}\n" .
                                    "Amount Due: Rs. " . number_format($record->amount_due, 2) .
                                    ($record->discount > 0
                                        ? "\nDiscount: Rs. " . number_format($record->discount, 2)
                                        : '') .
                                    "\nBalance: Rs. " . number_format($record->balance, 2)
                                ),

                            Forms\Components\TextInput::make('amount_paid')
                                ->label('Amount Being Paid (PKR)')
                                ->numeric()
                                ->prefix('Rs.')
                                ->default(fn () => $record->balance)
                                ->required(),

                            Forms\Components\Select::make('payment_method')
                                ->label('Payment Method')
                                ->options([
                                    'cash'          => '💵 Cash',
                                    'cheque'        => '🏦 Cheque',
                                    'bank_transfer' => '🔁 Bank Transfer',
                                    'jazzcash'      => '📱 JazzCash',
                                    'easypaisa'     => '📱 EasyPaisa',
                                ])
                                ->default('cash')
                                ->required(),

                            Forms\Components\DatePicker::make('paid_date')
                                ->label('Payment Date')
                                ->default(today())
                                ->required(),

                            Forms\Components\TextInput::make('fine')
                                ->label('Late Fine (PKR)')
                                ->numeric()
                                ->prefix('Rs.')
                                ->default(0),

                            Forms\Components\Textarea::make('remarks')
                                ->label('Remarks')
                                ->rows(2),
                        ];
                    })
                    ->modalHeading('Record Fee Payment')
                    ->action(function (FeeCollection $record, array $data): void {
                        $amountPaid = (float) $data['amount_paid'];
                        $fine       = (float) ($data['fine'] ?? 0);
                        $totalDue   = $record->amount_due - $record->discount + $fine;
                        $balance    = $totalDue - $amountPaid;

                        $status      = $balance <= 0 ? 'paid' : 'partial';
                        $statusLabel = $status === 'paid' ? '✅ Fully Paid' : '⚠️ Partial Payment';
                        $receiptNo   = FeeCollection::generateReceiptNumber();

                        $record->update([
                            'amount_paid'    => $amountPaid,
                            'fine'           => $fine,
                            'balance'        => max(0, $balance),
                            'status'         => $status,
                            'paid_date'      => $data['paid_date'],
                            'receipt_no'     => $receiptNo,
                            'payment_method' => $data['payment_method'],
                            'collected_by'   => auth()->id(),
                            'remarks'        => $data['remarks'] ?? null,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title("Payment recorded — {$statusLabel}")
                            ->body(
                                "Receipt No: {$receiptNo}\n" .
                                "Amount Paid: Rs. " . number_format($amountPaid, 2) .
                                ($balance > 0 ? "\nBalance Due: Rs. " . number_format($balance, 2) : '')
                            )
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                Tables\Actions\Action::make('apply_waiver')
                    ->label('Waive Fee')
                    ->icon('heroicon-o-gift')
                    ->color('warning')
                    ->visible(fn (FeeCollection $r) => $r->isPending())
                    ->form([
                        Forms\Components\Textarea::make('remarks')
                            ->label('Reason for Waiver')
                            ->required()
                            ->rows(2),
                    ])
                    ->requiresConfirmation()
                    ->action(function (FeeCollection $record, array $data): void {
                        $record->update([
                            'status'       => 'waived',
                            'amount_paid'  => 0,
                            'balance'      => 0,
                            'remarks'      => 'WAIVED: ' . $data['remarks'],
                            'collected_by' => auth()->id(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Fee waived successfully.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_overdue')
                        ->label('Mark as Overdue')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) =>
                            $records->where('status', 'pending')
                                ->each->update(['status' => 'overdue'])
                        ),
                ]),
            ])

            ->defaultSort('fee_month', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFeeCollections::route('/'),
            'create' => Pages\CreateFeeCollection::route('/create'),
            'edit'   => Pages\EditFeeCollection::route('/{record}/edit'),
        ];
    }
}