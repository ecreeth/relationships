<?php

namespace eCreeth\Relationships;

use Illuminate\Support\ServiceProvider;

class RelationshipsServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   *
   * @return void
   */
  public function register()
  {
    $this->commands([
      RelationshipsCommand::class
    ]);
  }

  /**
   * Bootstrap services.
   *
   * @return void
   */
  public function boot()
  {
    // 
  }
}
