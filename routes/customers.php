<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('vehicles', 'pages::vehicles.list')->name('vehicles.list');

    Route::livewire('customers', 'pages::customers.index')->name('customers.index');
    Route::livewire('customers/create', 'pages::customers.create')->name('customers.create');
    Route::livewire('customers/{customer}/edit', 'pages::customers.edit')->name('customers.edit');
    Route::livewire('customer/{customer}/cars', 'pages::vehicles.index')->name('vehicles.index');
    Route::livewire('customer/{customer}/cars/create', 'pages::vehicles.create')->name('vehicles.create');
    Route::livewire('customer/{customer}/cars/{vehicle}/edit', 'pages::vehicles.edit')->name('vehicles.edit');
});
