<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('policies', 'pages::policies.index')->name('policies.index');
    Route::livewire('policies/create', 'pages::policies.create')->name('policies.create');
    Route::livewire('policies/{policy}/edit', 'pages::policies.edit')->name('policies.edit');
});
