<?php

use App\Providers\AppServiceProvider;
use App\Providers\EducacaoSiteServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\PessoasServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    PessoasServiceProvider::class,
    EducacaoSiteServiceProvider::class,
];
