<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Post;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\PostResource\Pages;
use Asmit\FilamentMention\Forms\Components\RichMentionEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Split::make([
                    Section::make([
                        RichMentionEditor::make('content')
                            ->label('Content')
                            ->required()
                            ->triggerWith('@')
                            ->lookupKey('username')
                            ->titleField('name')
                            ->hintField('email')
                            ->mentionsItems(function () {
                                return User::all()->map(function ($user) {
                                    return [
                                        'id' => $user->id,
                                        'username' => $user->username,
                                        'name' => $user->name,
                                        'email' => $user->email,
                                        'avatar' => $user->profile,
                                        'url' => 'admin/users/' . $user->id,
                                    ];
                                })->toArray();
                            })
                    ]),
                    Section::make([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('description')
                            ->required()
                            ->maxLength(255),

                        FileUpload::make('feature_image')
                            ->label('Feature Image')
                            ->directory(config('app.feature_image_dir'))
                            ->nullable(),

                        Forms\Components\Select::make('tags')
                            ->label('Tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(50),
                            ]),

                        Forms\Components\Select::make('status')
                            ->options([
                                'approved' => 'Approved',
                                'disapproved' => 'Disapproved',
                            ])
                            ->required(),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Published'),
                    ])->grow(false)->columnSpan(1)->extraAttributes(['class' => 'max-w-sm']),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->searchable(),
                ImageColumn::make('feature_image')
                    ->label('Feature Image')
                    ->disk('public')
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(10),

                TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->limit(10),

                TextColumn::make('tags.name')
                    ->label('Tags')
                    ->badge()
                    ->separator(', '),

                IconColumn::make('is_published')
                    ->sortable()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle')
                    ->label('Is Published'),

                SelectColumn::make('status')
                    ->options([
                        'approved' => 'Approved',
                        'disapproved' => 'Disapproved',
                    ])
                    ->sortable(),
                TextColumn::make('author.name')->label('Author')->sortable()->searchable(),
                TextColumn::make('created_at')
                    ->sortable()
                    ->dateTime('F j, Y, g:i a'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'approved' => 'Approved',
                        'disapproved' => 'Disapproved',
                    ])
                    ->label('Status'),

                SelectFilter::make('is_published')
                    ->label('Is Published'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
