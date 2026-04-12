<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about-front', function () {
    $this->info('Front-end demonstrativo do Sistema Financeiro Interno.');
})->purpose('Descreve o front-end do projeto');
