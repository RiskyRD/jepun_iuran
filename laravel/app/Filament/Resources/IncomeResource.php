<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeResource\Pages;
use App\Models\Income;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class IncomeResource extends Resource
{
    protected static ?string $model = Income::class;
    protected static ?string $navigationGroup = 'Payments';
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';

    protected static function getMonthOptions(): array {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = \Carbon\Carbon::create()->month($i)->format('M');
        }
        return $months;
    }

    public static function generatePaymentOptionsIncome($userId, $category, $year)
    {
        $options = [];

        if (!$userId) {
            return $options;
        }
        
        $payments = \App\Models\Income::where('user_id', $userId)
            ->where('category', $category)
            ->whereYear('income_date', $year)
            ->get();

        $paidMonths = $payments->map(function ($payment) {
            return \Carbon\Carbon::parse($payment->income_date)->month;
        })->toArray();

        for ($i = 1; $i <= 12; $i++) {
            $monthName = \Carbon\Carbon::create()->month($i)->format('M');
            $options[$i] = [
                'label' => $monthName,
                'disabled' => false,
                'checked' => in_array($i, $paidMonths),
            ];
        }

        return $options;
    }
    public static function calculateArrears($userId, $category)
    {
        $arrears = 0;
        $currentDate = now();

        // Find the first income date for the user in the given category
        $firstPaymentDate = \App\Models\Income::where('user_id', $userId)
            ->where('category', $category)
            ->orderBy('income_date', 'asc')
            ->value('income_date');

        // Default to January 1, 2024, if no payments exist
        $startOfPayment = $firstPaymentDate ? \Carbon\Carbon::parse($firstPaymentDate) : \Carbon\Carbon::createFromDate(2024, 1, 1);

        // Calculate how many months have passed since the first payment date
        $monthsPassed = ceil($startOfPayment->diffInMonths($currentDate));

        // Fetch the user's total payments for the given category
        $user = \App\Models\User::withCount([$category . 'Payments'])->find($userId);
        $paymentCount = $user->{$category . '_payments_count'} ?? 0;

        // Calculate arrears (tunggakan)
        $arrears = $monthsPassed - $paymentCount;
        return max(0, (int) floor($arrears));
    }

    public static function form(Form $form): Form
    {
        $isEditing = !is_null($form->getRecord());

        
        $schema = [
            Forms\Components\Select::make('user_id')
                ->label('User')
                ->options(\App\Models\User::pluck('name', 'id'))
                ->required()
                ->searchable()
                ->preload()
                ->reactive() 
                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                    
                    $year = $get('year') ?? now()->year;

                    if ($state) {
                        
                        $wajibOptions = static::generatePaymentOptionsIncome($state, 'wajib', $year);
                        $sampahOptions = static::generatePaymentOptionsIncome($state, 'sampah', $year);

                        $wajibChecked = array_keys(array_filter($wajibOptions, fn ($option) => $option['checked']));
                        $sampahChecked = array_keys(array_filter($sampahOptions, fn ($option) => $option['checked']));
                        
                        $set('wajibPayments', $wajibChecked);
                        $set('sampahPayments', $sampahChecked);

                        $arrearWajib = static::calculateArrears($state, 'wajib');
                        $arrearSampah = static::calculateArrears($state, 'sampah');

                        $set('arrear_wajib', $arrearWajib);
                        $set('arrear_sampah', $arrearSampah);
                    }
                }),
            Forms\Components\Select::make('category')
                ->options([
                    'wajib_sampah' => 'Wajib + Sampah',
                    'wajib' => 'Wajib',
                    'sampah' => 'Sampah',
                    'kompensasi_jalan' => 'Kompensasi Jalan',
                    'bunga_lpd' => 'Bunga LPD',
                    'lainnya' => 'Lainnya',
                ])
                ->required()
                ->reactive() 
                ->afterStateUpdated(function (callable $set, $state) {
                    switch ($state) {
                        case 'wajib_sampah':
                            $set('nominal', 25000);
                            $set('isNominalReadonly', true); 
                            break;
                        case 'wajib':
                            $set('nominal', 5000);
                            $set('isNominalReadonly', true);
                            break;
                        case 'sampah':
                            $set('nominal', 20000);
                            $set('isNominalReadonly', true);
                            break;
                        default:
                            $set('nominal', 0);
                            $set('isNominalReadonly', false);
                            break;
                    }
                }),
            Forms\Components\TextInput::make('nominal')
                ->numeric()
                ->reactive()
                ->required()
                ->readOnly(fn ($get) => $get('isNominalReadonly')),
            Forms\Components\DatePicker::make('income_date')
                ->default(now()->toDateString()),
            Forms\Components\Textarea::make('description'),        
            Forms\Components\Fieldset::make('Payments')
                ->label('Payments Info')
                ->schema([
                    Forms\Components\Select::make('year')
                        ->options(function () {
                            $currentYear = now()->year;
                            $years = [];
                            for ($year = 2024; $year <= $currentYear; $year++) {
                                $years[$year] = (string) $year;
                            }
                            
                            return $years;
                        })
                        ->default(now()->year)
                        ->reactive() 
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) { 
                            $userId = $get('user_id');

                            if ($userId) {
                                $wajibOptions = static::generatePaymentOptionsIncome($userId, 'wajib', $state);
                                $sampahOptions = static::generatePaymentOptionsIncome($userId, 'sampah', $state);

                                $set('wajibPayments', array_keys(array_filter($wajibOptions, fn ($option) => $option['checked'])));
                                $set('sampahPayments', array_keys(array_filter($sampahOptions, fn ($option) => $option['checked'])));
                            }
                        }),
                    Forms\Components\Fieldset::make('Payments_check')
                        ->schema([
                            Forms\Components\CheckboxList::make('wajibPayments')
                                ->options(static::getMonthOptions())
                                ->columns(4)
                                ->disabled(),
                            Forms\Components\CheckboxList::make('sampahPayments')
                                ->options(static::getMonthOptions())
                                ->columns(4)
                                ->disabled(),
                        ])
                ]),

                Forms\Components\Fieldset::make('Arrear Info')
                ->schema([
                    Forms\Components\TextInput::make('arrear_wajib')
                        ->label('Wajib Arrears')
                        ->readOnly()
                        ->default(0)
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            $userId = $get('user_id');
                            if ($userId) {
                                $arrears = static::calculateArrears($userId, 'wajib');
                                $set('arrear_wajib', $arrears);
                            }
                        }),
                    Forms\Components\TextInput::make('arrear_sampah')
                        ->label('Sampah Arrears')
                        ->readOnly()
                        ->default(0)
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            $userId = $get('user_id');
                            if ($userId) {
                                $arrears = static::calculateArrears($userId, 'sampah');
                                $set('arrear_sampah', $arrears);
                            }
                        }),
                ]),
        ];

        if (!$isEditing) {
            $schema[] = Forms\Components\TextInput::make('amount')
                ->label('Number of Records')
                ->numeric()
                ->default(1)
                ->required();
        }

        return $form->schema($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User'),
                TextColumn::make('user.gang.gang_name')
                    ->label('Gang'),
                TextColumn::make('category'),
                TextColumn::make('method'),
                TextColumn::make('nominal')
                    ->formatStateUsing(fn (string $state): string => number_format($state, 0, ',', '.')),
                TextColumn::make('income_date')
                    ->label('Date')
                    ->sortable(),
                TextColumn::make('description'),
            ])
            ->defaultSort('income_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('User'),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'wajib' => 'Wajib',
                        'sampah' => 'Sampah',
                        'kompensasi_jalan' => 'Kompensasi Jalan',
                        'bunga_lpd' => 'Bunga LPD',
                        'lainnya' => 'Lainnya',
                    ]),
                Tables\Filters\Filter::make('income_date')
                ->form([
                    Forms\Components\DatePicker::make('income_date')->label('Date'),
                ])
                ->query(function ($query, $data) {
                    if ($data['income_date']) {
                        return $query->whereDate('income_date', $data['income_date']);
                    }
                    return $query;
                })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncomes::route('/'),
            'create' => Pages\CreateIncome::route('/create'),
            'edit' => Pages\EditIncome::route('/{record}/edit'),
        ];
    }
}
