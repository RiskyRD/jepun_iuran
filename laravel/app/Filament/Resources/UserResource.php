<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Income;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'People';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static function getMonthOptions(): array
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = \Carbon\Carbon::create()->month($i)->format('M');
        }
        return $months;
    }

    public static function generatePaymentOptionsUser($userId, $category, $year)
    {
        $options = [];

        if (!$userId) {
            return $options;
        }

        // Fetch all payment dates for the given user, category, and year
        $payments = Income::where('user_id', $userId)
            ->where('category', $category)
            ->whereYear('income_date', $year)
            ->pluck('income_date')
            ->map(function ($date) {
                return \Carbon\Carbon::parse($date)->format('Y-m');
            })
            ->toArray();

        // Iterate through each month of the year
        for ($i = 1; $i <= 12; $i++) {
            $monthName = \Carbon\Carbon::create()->month($i)->format('M');
            $monthKey = sprintf('%s-%02d', $year, $i); // e.g., "2024-01"

            $options[$i] = [
                'label' => $monthName,
                'disabled' => in_array($monthKey, $payments),
                'checked' => in_array($monthKey, $payments),
            ];
        }

        return $options;
    }
    

    public static function form(Form $form): Form
    {
        // Determine if we're editing a record
        $isEditing = !is_null($form->getRecord());

        // Initialize the schema array
        $schema = [
            Forms\Components\TextInput::make('email')
                ->required(),
            Forms\Components\TextInput::make('password')
                ->password()
                ->required($isEditing ? false : true), // Only required in Create mode
            Forms\Components\TextInput::make('name')
                ->required(),
            Forms\Components\Select::make('gang_id')
                ->label('Gang')
                ->options(\App\Models\Gang::pluck('gang_name', 'id'))
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('telephone'),
            Forms\Components\Select::make('roles')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload()
                ->searchable()
        ];

        if ($isEditing) {
            $schema[] = 
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
                                $userId = $get('id');
        
                                if ($userId) {
                                    $wajibOptions = static::generatePaymentOptionsUser($userId, 'wajib', $state);
                                    $sampahOptions = static::generatePaymentOptionsUser($userId, 'sampah', $state);
        
                                    $set('wajibPayments', array_keys(array_filter($wajibOptions, fn ($option) => $option['checked'])));
                                    $set('sampahPayments', array_keys(array_filter($sampahOptions, fn ($option) => $option['checked'])));
                                }
                            }),
        
                        Forms\Components\Fieldset::make('Payments_check')
                            ->schema([
                                Forms\Components\CheckboxList::make('wajibPayments')
                                    ->label('Wajib Payments') 
                                    ->options(static::getMonthOptions())
                                    ->columns(4)
                                    ->disabled(),
                                
                                Forms\Components\CheckboxList::make('sampahPayments')
                                    ->label('Sampah Payments') 
                                    ->options(static::getMonthOptions())
                                    ->columns(4)
                                    ->disabled(),
                            ]),
        
                        // Arrear form section
                        Forms\Components\Fieldset::make('Arrear Info')
                            ->schema([
                                Forms\Components\TextInput::make('arrear_wajib')
                                    ->label('Wajib Arrears')
                                    ->readOnly()
                                    ->default(0)
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        $userId = $get('id');
                                        if ($userId) {
                                            $arrears = static::calculateArrears($userId, 'wajib', $get('year'));
                                            $set('arrear_wajib', $arrears);
                                        }
                                    }),
        
                                Forms\Components\TextInput::make('arrear_sampah')
                                    ->label('Sampah Arrears')
                                    ->readOnly()
                                    ->default(0)
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        $userId = $get('id');
                                        if ($userId) {
                                            $arrears = static::calculateArrears($userId, 'sampah', $get('year'));
                                            $set('arrear_sampah', $arrears);
                                        }
                                    }),
                            ])
                    ]);
        }
        

        return $form->schema($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('gang.gang_name')
                    ->label('Gang'),
                TextColumn::make('telephone')
                    ->searchable(),
                TextColumn::make('status'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gang_id')
                    ->label('Gang')
                    ->options(\App\Models\Gang::pluck('gang_name', 'id'))
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
