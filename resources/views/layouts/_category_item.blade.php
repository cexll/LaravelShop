@if(isset($category['children']) && count($category['children']) > 0)
    <a href="{{ route('products.index', ['category_id' => $category['id']]) }}" class="dropdown-item" >
        {{ $category['name'] }}

    </a>
    @each('layouts._category_item', $category['children'], 'category')


@else
    <li><a href="{{ route('products.index', ['category_id' => $category['id']]) }}"></a></li>
@endif
