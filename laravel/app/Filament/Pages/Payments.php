<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables;
use App\Models\Income;

class Payments extends Page implements Forms\Contracts\HasForms, HasTable
{
    use Forms\Concerns\InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static string $view = 'filament.pages.payments';
    protected static ?string $navigationGroup = 'Personal'; 

    public int $year;
    public array $sampahPayments = [];
    public array $wajibPayments = [];
    public int $arrear_wajib = 0;
    public int $arrear_sampah = 0;

    protected static function getMonthOptions(): array {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = \Carbon\Carbon::create()->month($i)->format('M');
        }
        return $months;
    }

    public static function generatePaymentOptions($userId, $category, $year)
    {
        // Generate payment options based on the user's payment history
        $options = [];
        if (!$userId) return $options;

        $user = \App\Models\User::withCount([$category . 'Payments'])->find($userId);
        $paymentsPerYear = 12; 
        $paymentCount = $user->{$category . '_payments_count'} ?? 0;
        $remainingPayments = $paymentCount - (($year - 2024) * $paymentsPerYear);

        for ($i = 1; $i <= 12; $i++) {
            $monthName = now()->month($i)->format('M');
            $options[$i] = [
                'label' => $monthName,
                'disabled' => true,
                'checked' => $remainingPayments > 0 ? $i <= $remainingPayments : false,
            ];
        }

        return $options;
    }

    public static function calculateArrears($userId, $category)
    {
        // Calculate arrears for the given category
        $arrears = 0;
        $currentDate = now();
        $startOfYear = \Carbon\Carbon::createFromDate(2024, 1, 1);
        $monthsPassed = ceil($startOfYear->diffInMonths($currentDate));

        $user = \App\Models\User::withCount([$category . 'Payments'])->find($userId);
        $paymentCount = $user->{$category . '_payments_count'} ?? 0;

        $arrears = $monthsPassed - $paymentCount;
        return max(0, (int) floor($arrears));
    }

    protected function getFormSchema(): array
    {
        return [
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
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $userId = Auth::id();
                            if ($userId) {
                                $wajibOptions = static::generatePaymentOptions($userId, 'wajib', $state);
                                $sampahOptions = static::generatePaymentOptions($userId, 'sampah', $state);

                                $set('wajibPayments', array_keys(array_filter($wajibOptions, fn ($option) => $option['checked'])));
                                $set('sampahPayments', array_keys(array_filter($sampahOptions, fn ($option) => $option['checked'])));
                                $set('arrear_wajib', static::calculateArrears($userId, 'wajib'));
                                $set('arrear_sampah', static::calculateArrears($userId, 'sampah'));
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
                        ->default(0),
                    Forms\Components\TextInput::make('arrear_sampah')
                        ->label('Sampah Arrears')
                        ->readOnly()
                        ->default(0),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Income::query()->where('user_id', Auth::id()))
            ->columns([
                TextColumn::make('user.name')
                    ->label('User'),
                TextColumn::make('user.gang')
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
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }

    public function mount(): void
    {
        \Log::info('Mount method called');
        $userId = Auth::id(); // Get the currently logged-in user ID
        $currentYear = now()->year;

        if ($userId) {
            // Initialize payment options for both categories
            $wajibOptions = static::generatePaymentOptions($userId, 'wajib', $currentYear);
            $sampahOptions = static::generatePaymentOptions($userId, 'sampah', $currentYear);

            // Ensure the options contain 'checked' status properly
            $wajibChecked = array_keys(array_filter($wajibOptions, fn($option) => $option['checked']));
            $sampahChecked = array_keys(array_filter($sampahOptions, fn($option) => $option['checked']));

            $arrearsWajib = static::calculateArrears($userId, 'wajib');
            $arrearsSampah = static::calculateArrears($userId, 'sampah');

            // Set the initial values for the checkboxes based on the payment options
            $this->form->fill([
                'year' => $currentYear,
                'wajibPayments' => $wajibChecked, // Set these directly for testing
                'sampahPayments' => $sampahChecked,
                'arrear_wajib' => $arrearsWajib,
                'arrear_sampah' => $arrearsSampah,
            ]);
        }
        // dd($this->form);
    }
}
