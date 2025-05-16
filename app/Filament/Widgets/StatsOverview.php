<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Role;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Users', User::count())
                ->description('Active users in the system')
                ->icon('heroicon-o-users'),
            Stat::make('Posts', Post::count())
                ->description('Published content items')
                ->icon('heroicon-o-document'),
            Stat::make('Tags', Tag::count())
                ->description('Content categorization tags')
                ->icon('heroicon-o-tag'),
        ];
    }
}
