<?php

/**
 * Usage:
 *  1. "composer install" to install the dependencies.
 *
 *  2. Set the parameters of test environment.
 *    TEST_HOSTNAME
 *    TEST_PROTOCOL
 *    WITH_DEBUG
 *
 *  3. Define test cases.
 *    [
 *      'method',
 *      'uri',
 *      'query parameters in array',
 *      'post data in array',
 *      'HTTP headers in array',
 *    ]
 *
 *  4. Command
 *    Examples:
 *      1. php run.php    -    Run all test cases.
 *      2. php run 2 3    -    Run the 2nd and 3rd test case defined in the array.
 *
 *  Test Case 1 will aquire the authentication token, and persist it into a file,
 *  then other test cases will read the token from that file. If the token expires,
 *  need to run Test Case 1 again.
 *
 * @author Hailong Zhao <hailongzh@hotmail.com>
 */

require __DIR__ . '/SyliusShopAPITestDriver.php';

const TEST_HOSTNAME = 'localhost';
const TEST_PROTOCOL = 'http';
const WITH_DEBUG    = true;

TestCases([
    // Test Case 1
    [
        'post',
        '/shop-api/login_check',
        [],
        [
            'code' => 'placeholder',
        ],
    ],
    // Test Case 2
    [
        'get',
        '/shop-api/me',
    ],
    // Test Case 3
    [
        'post',
        '/shop-api/carts/placeholder/items',
        [],
        [
            'channel' => 'US_WEB',
            'productCode' => 'thecode',
        ],
    ],
    // Test Case 4
    [
        'put',
        '/shop-api/checkout/placeholder/address',
        [
        ],
        [
            'shippingAddress' => [
                'firstName' => 'Hailong',
                'lastName' => 'Zhao',
                'city' => 'Beijing',
                'street' => 'Jianguo Road',
                'countryCode' => 'CN',
                'postcode' => '100002',
                'provinceName' => 'Beijing',
            ],
        ],
    ],
]);
