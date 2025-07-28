<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class ProductController extends Controller
{
 public function __construct()
{
    $this->middleware('auth:api')->except(['index', 'show']);
    
    // Handle method not allowed
    $this->middleware(function ($request, $next) {
        try {
            return $next($request);
        } catch (MethodNotAllowedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
        }
    });
    
    if (App::environment('production') && !config('app.api_enabled')) {
        abort(403, 'API access is disabled in production');
    }
}

    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $products = Product::latest()->paginate(5);
        return new ProductResource(true, 'List Data Products', $products);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'image'         => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'price'         => 'required|numeric|min:0',
            'stock'         => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Upload image
        $imagePath = $request->file('image')->store('products', 'public');

        // Create product
        $product = Product::create([
            'image'         => basename($imagePath),
            'title'         => $request->title,
            'description'   => $request->description,
            'price'         => $request->price,
            'stock'         => $request->stock,
        ]);

        // Generate JWT token for the response
        $token = JWTAuth::fromUser($request->user());

        return (new ProductResource(true, 'Data Product Berhasil Ditambahkan!', $product))
            ->additional([
                'meta' => [
                    'token' => $token,
                ]
            ]);
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return new ProductResource(true, 'Detail Data Product!', $product);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'image'         => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'         => 'sometimes|string|max:255',
            'description'   => 'sometimes|string',
            'price'         => 'sometimes|numeric|min:0',
            'stock'         => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Handle image update if provided
        if ($request->hasFile('image')) {
            // Delete old image
            Storage::disk('public')->delete('products/'.$product->image);
            
            // Store new image
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = basename($imagePath);
        }

        // Update other fields
        $product->title = $request->input('title', $product->title);
        $product->description = $request->input('description', $product->description);
        $product->price = $request->input('price', $product->price);
        $product->stock = $request->input('stock', $product->stock);
        
        $product->save();

        return new ProductResource(true, 'Data Product Berhasil Diupdate!', $product);
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // Delete associated image
        Storage::disk('public')->delete('products/'.$product->image);
        
        $product->delete();

        return new ProductResource(true, 'Data Product Berhasil Dihapus!', null);
    }
}