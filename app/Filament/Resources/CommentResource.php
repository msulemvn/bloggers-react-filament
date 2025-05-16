<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Models\Comment;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('User')
                ->relationship('user', 'name')
                ->searchable()
                ->nullable(),

            Forms\Components\MorphToSelect::make('commentable')
                ->types([
                    Forms\Components\MorphToSelect\Type::make('post')->titleColumnName('title'),
                    // Add other commentable models as needed
                ])
                ->required(),

            Forms\Components\Textarea::make('body')
                ->label('Comment')
                ->required()
                ->rows(4),

            Forms\Components\Select::make('parent_comment_id')
                ->label('Parent Comment')
                ->relationship('parent', 'body')
                ->searchable()
                ->nullable(),

            Forms\Components\Select::make('status')
                ->options([
                    'approved' => 'Approved',
                    'pending' => 'Pending',
                    'rejected' => 'Rejected',
                ])
                ->default('approved')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('commentable_type')
                    ->label('Model')
                    ->sortable(),

                Tables\Columns\TextColumn::make('body')
                    ->limit(50)
                    ->wrap()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'approved' => 'Approved',
                        'pending' => 'Pending',
                        'rejected' => 'Rejected',
                    ])
                    ->sortable()
                    ->rules(['required', 'in:approved,pending,rejected']),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'approved' => 'Approved',
                        'pending' => 'Pending',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('commentable_type')
                    ->label('Model')
                    ->options([
                        'App\\Models\\Post' => 'Post',
                        // Add others here if needed
                    ]),
            ])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComments::route('/'),
        ];
    }
}
