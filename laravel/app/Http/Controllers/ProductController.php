<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Image;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // read all product
        // return Product::all();
        return Product::with('createdByUser', 'updatedByUser')->orderBy('id', 'desc')->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /** Sanctum authentication */
        $user = auth()->user();

        $request->validate([
            'name' => 'required',
            'slug' => 'required',
            'price' => 'required'
        ]);


        $newProduct = array(
            'created_by_user' => $user->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'slug' => $request->input('slug'),
            'price' => $request->input('price')
        );
        
        /** check, Did user attach a image file ? */
        $imageData = $request->file('file');

        if($imageData){
            /** change file name */
            $file_name = 'product_'.time().'.'.$imageData->getClientOriginalExtension(); // $imageData->getClientOriginalExtension() is surname (.png .jpeg)

            /** set size image */
            $imgWidth = 400;
            $imgHeight = 400;

            /** public_path = /var/www/public */
            $folderThumbnailUpload = public_path('/images/products/thumbnail');
            $thumbnailPath = $folderThumbnailUpload."/".$file_name;

            /** upload into thumbnail folder */ 
            $img = Image::make($imageData->getRealPath());

            /** fit = crop and resize */ 
            $img->orientate()->fit($imgWidth, $imgHeight, function ($constraint) {
                // add callback functionality to retain maximal original image size
                $constraint->upsize();
            });
            $img->save($thumbnailPath);

            /** move image to original folder */ 
            $folderOriginalUpload = public_path('/images/products/original');
            $imageData->move($folderOriginalUpload, $file_name);

            /** url = localhost or domain name */
            $newProduct['image'] = url('/').'/images/products/thumbnail/'.$file_name;
            
        }else {
            $newProduct['image'] = url('/').'/images/products/thumbnail/no_img.png';
        }

        /** insert $newProduct to database */
        Product::create($newProduct);
 
        return response($newProduct, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return $product;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {   
        /** Sanctum authentication */
        $user = auth()->user();

        $request->validate([
            'name' => 'required',
            'slug' => 'required',
            'price' => 'required'
        ]);


        $newProduct = array(
            'updated_by_user' => $user->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'slug' => $request->input('slug'),
            'price' => $request->input('price')
        );
        
        /** check, Did user attach a image file ? */
        $imageData = $request->file('file');

        if($imageData){
            /** change file name */
            $file_name = 'product_'.time().'.'.$imageData->getClientOriginalExtension(); // $imageData->getClientOriginalExtension() is surname (.png .jpeg)

            /** set size image */
            $imgWidth = 400;
            $imgHeight = 400;

            /** public_path = /var/www/public */
            $folderThumbnailUpload = public_path('/images/products/thumbnail');
            $thumbnailPath = $folderThumbnailUpload."/".$file_name;

            /** upload into thumbnail folder */ 
            $img = Image::make($imageData->getRealPath());

            /** fit = crop and resize */ 
            $img->orientate()->fit($imgWidth, $imgHeight, function ($constraint) {
                // add callback functionality to retain maximal original image size
                $constraint->upsize();
            });
            $img->save($thumbnailPath);

            /** move image to original folder */ 
            $folderOriginalUpload = public_path('/images/products/original');
            $imageData->move($folderOriginalUpload, $file_name);

            /** url = localhost or domain name */
            $newProduct['image'] = url('/').'/images/products/thumbnail/'.$file_name; 
        }

        $product->update($newProduct);
        return response($product, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
        return $product->delete();
    }

    /**
     * Search function
     * @param string $keyword
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function searchProductName($keyword){
        return response(
            Product::with('createdByUser', 'updatedByUser')
                    ->where('name', 'like', '%'.$keyword.'%')
                    ->orderBy('id', 'desc')
                    ->paginate(10),
            200
        );
    }
}
