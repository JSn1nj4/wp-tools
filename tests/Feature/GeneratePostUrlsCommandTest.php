<?php

it('has generateposturlscommand page', function () {
    $response = $this->get('/generateposturlscommand');

    $response->assertStatus(200);
});
