<?php

namespace App\Console\Commands;

class LibermediaImport extends HerreraImport
{
    protected $signature = 'libermedia:import {--source-connection=libermedia_source} {--prefix=oc_} {--only=catalog,content,customers,billing_addresses,pricing,orders,seo,relations,archive} {--dry-run} {--final-sync}';

    protected $description = 'Import local Libermedia OpenCart data using the existing migration engine';
}
