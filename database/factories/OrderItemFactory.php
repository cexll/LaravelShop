<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\OrderItem;
use Faker\Generator as Faker;

$factory->define(OrderItem::class, function (Faker $faker) {
    // 随机取一条商品
    $product = \App\Models\Product::query()->where('on_sale', true)->inRandomOrder()->first();
    // 该商品的SKU中随机取一条
    $sku = $product->skus()->inRandomOrder()->first();

    return [
        'amount' => random_int(1, 5), // 购买数量随机 1 - 5
        'price' => $sku->price,
        'rating' => null,
        'review' => null,
        'reviewed_at' => null,
        'product_id' => $product->id,
        'product_sku_id' => $sku->id,
    ];
});
