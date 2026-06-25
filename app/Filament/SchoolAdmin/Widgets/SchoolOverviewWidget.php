<?php

namespace App\Filament\SchoolAdmin\Widgets;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SchoolOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $currentYear = AcademicYear::where('is_current', true)->first();

        return [
            Stat::make('Total Students', User::where('role', 'student')->count())
                ->description('Enrolled students')
                ->icon('heroicon-o-academic-cap')
                ->color('success'),

            Stat::make('Total Teachers', User::where('role', 'teacher')->count())
                ->description('Teaching staff')
                ->icon('heroicon-o-user-group')
                ->color('info'),

            Stat::make('Classes', SchoolClass::count())
                ->description('Grades defined')
                ->icon('heroicon-o-building-library')
                ->color('warning'),

            Stat::make(
                'Current Year',
                $currentYear ? $currentYear->name : 'Not set'
            )
                ->description(
                    $currentYear
                    ? Section::where('academic_year_id', $currentYear->id)->count() . ' active sections'
                    : 'Go to Academic Years to set one'
                )
                ->icon('heroicon-o-calendar-days')
                ->color($currentYear ? 'primary' : 'danger'),
        ];
    }
}
