<?php

it('loads styles panel')
    ->skip()
    ->actingAsAdmin()
    ->get('/keystone/components/edit')
    ->assertOk();
