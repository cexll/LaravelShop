<?php


namespace App\Services;


use App\Models\Product;
use App\SearchBuilders\ProductSearchBuilder;

class ProductService
{
    public function getSimilarProductIds(Product $product, $amount)
    {
        if (count($product->properties) === 0) {
            return [];
        }

        $builder = (new ProductSearchBuilder())->onSale()->paginate($amount, 1);
        foreach ($product->properties as $property) {
            $builder->propertyFilter($property->name, $property->value, 'should');
        }
        $builder->minShouldMatch(ceil(count($product->properties) / 2));
        $params = $builder->getParams();
        $params['body']['query']['bool']['must_not'] = [['term'=>['_id'=>$product->id]]];
        $request = app('es')->search($params);

        return collect($request['hits']['hits'])->pluck('_id')->all();
    }
}
