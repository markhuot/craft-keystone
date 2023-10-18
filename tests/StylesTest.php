<?php

it('loads styles panel')
    ->actingAsAdmin()
    ->get('/keystone/components/edit')
    ->assertOk();
