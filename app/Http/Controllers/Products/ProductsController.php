<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class ProductsController extends Controller
{
    use \App\Classes\Tools\CacheTools;
    use \App\Classes\Vendors\VendorManager;
    use \App\Classes\Products\ProductManager;

    public function all(Request $request)
    {
        $by = $request->get('by','asc');
        $sort = $request->get('sort','name');

        return view('products.index')->with([
            'arProducts' => $this->getAllProducts(10, $sort, $by),
            'title' => Lang::get('products.titleAll'),
            'by' => $by == 'asc' ? 'desc' : 'asc',
            'setToken' => true
        ]);
    }

    public function editProduct($id, Request $request)
    {
        $request->validate([
            'price' => 'required|numeric|min:1'
        ]);

        if ($this->updateProductFromRequest($id, $request)){
            return response()->json(['success' => "Продукт под номером $id успешно обновлен"]);
        }else{
            return response()->json(['error' => 'Ошибка обновления продукта']);
        }
    }

    private function updateProductFromRequest($id, Request $request)
    {
        $product = Product::find($id);
        $bNeedSave = false;

        if ($price = $request->get('price')) {
            $product->price = $price;
            $bNeedSave = true;
        }

        if ($bNeedSave) {
            $product->save();
            return true;
        }

        return false;
    }
}
