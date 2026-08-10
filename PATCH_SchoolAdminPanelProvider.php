<?php

/*
|--------------------------------------------------------------------------
| PATCH: Add this to SchoolAdminPanelProvider.php inside panel()
|--------------------------------------------------------------------------
| Find the ->navigationGroups([...]) block and add 'Academics' group.
| Copy the full updated array below:
*/

// Replace the existing ->navigationGroups([...]) with this:

/*
->navigationGroups([
    NavigationGroup::make('Academic Setup')
        ->icon('heroicon-o-academic-cap'),
    NavigationGroup::make('People')
        ->icon('heroicon-o-users')
        ->collapsed(),
    NavigationGroup::make('Academics')
        ->icon('heroicon-o-clipboard-document-check'),
    NavigationGroup::make('Finance')
        ->icon('heroicon-o-banknotes')
        ->collapsed(),
    NavigationGroup::make('Communication')
        ->icon('heroicon-o-chat-bubble-left-ellipsis')
        ->collapsed(),
]),
*/
