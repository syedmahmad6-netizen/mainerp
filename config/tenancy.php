<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Domain
    |--------------------------------------------------------------------------
    | The root domain of Gnosis. All school subdomains are resolved against this.
    | Example: "demo.gnosis.ac.pk" → subdomain = "demo"
    */
    'base_domain' => env('TENANT_BASE_DOMAIN', 'gnosis.ac.pk'),

    /*
    |--------------------------------------------------------------------------
    | Super Admin Domain
    |--------------------------------------------------------------------------
    | The main domain where the platform owner (you) logs in.
    | No tenant context exists here.
    */
    'super_admin_domain' => env('SUPER_ADMIN_DOMAIN', 'gnosis.ac.pk'),

];
