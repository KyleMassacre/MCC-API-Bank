<?php

APIRoute::version('v1', function ($api) {
    $api->group(['namespace' => 'Modules\Bank\Http\Controllers', 'middleware' => ['cors','jwt.auth']], function ($api) {
        $api->group(['prefix' => 'cyberbank'], function($api) {
            $api->get('buy',['as' => 'cyber.buy','uses' => 'CyberBankController@getBuyCyberBank']);
            $api->post('deposit',['as' => 'cyber.deposit', 'uses' => 'CyberBankController@postDeposit']);
            $api->post('withdraw',['as' => 'cyber.withdraw', 'uses' => 'CyberBankController@postWithdraw']);
        });

        $api->group(['prefix' => 'bank'], function($api) {
            $api->get('buy',['as' => 'bank.buy','uses' => 'BankController@getBuyBank']);
            $api->post('deposit',['as' => 'bank.deposit','uses' => 'BankController@postDeposit']);
            $api->post('withdraw',['as' => 'bank.withdraw','uses' => 'BankController@postWithdraw']);



        });
    });
});