<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Image;

class AuthController extends Controller
{
    /**
     * registor 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        //
        $request->validate([
            'fname'=>'required|string',
            'lname'=>'required|string',
            'username'=>'required|string',
            'password'=>'required|string|confirmed',
            'email'=>'required|string|unique:users,email'
        ]);

        $encryptPassword = bcrypt($request->password); // encrypt password
        $user = User::create([
                    'fname'=>$request->fname,
                    'lname'=>$request->lname,
                    'username'=>$request->username,
                    'password'=>$encryptPassword,
                    'email'=>$request->email,
                    'tel'=>$request->tel,
                    'avatar'=> url('/').'/images/products/thumbnail/no_img.png'
        ]);
        return response($user, 201);
    }

    /**
     * login
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        //
        $request->validate([
            'email'=>'required|string',
            'password'=>'required|string',
        ]);

        //check email password
        $user = User::where('email', $request->email)->first();
        
        if( $user && Hash::check($request->password, $user->password) ){
            // delete old token
            $user->tokens()->delete();

            // create sanctum token
            $token = $user->createToken($request->userAgent(), ["$user->roles"])->plainTextToken;
            $res = [
                'user' => $user,
                'token' => $token
            ];

            return response($res, 200);

        }else{
            return response([ 'message'=>'Your email/password is incorrect!' ], 401);
        }
    }

    /**
     * logout
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        return [ 'message'=>'Logged out' ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        //Check role admin(1) member(0)
        $userAuth = auth()->user();
        if( !$userAuth->tokenCan("1") ){
            return response(["message"=>"Permission denied"], 403);
        }

        return User::all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Check role admin(1) member(0)
        $user = auth()->user();
        if( !$user->tokenCan("1") ){
            return response(["message"=>"Permission denied"], 403);
        }

        $request->validate([
            'fname'=>'required|string',
            'lname'=>'required|string',
            'username'=>'required|string',
            'password'=>'required|string|confirmed',
            'email'=>'required|string|unique:users,email'
        ]);

        $request->password = bcrypt($request->password);

        return response( User::create($request->all()), 201 );

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {   
        //Check role admin(1) member(0)
        $userAuth = auth()->user();
        if( !$userAuth->tokenCan("1") ){
            return response(["message"=>"Permission denied"], 403);
        }
        return $user;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //Check role admin(1) member(0) or edit themselves
        $userAuth = auth()->user();
        if( !$userAuth->tokenCan("1") && $userAuth->id != $user->id ){
            return response(["message"=>"Permission denied"], 403);
        }

        $request->validate([
            'fname'=>'required|string',
            'lname'=>'required|string',
            'username'=>'required|string'
        ]);

        $user->update([
                    'fname'=>$request->fname,
                    'lname'=>$request->lname,
                    'username'=>$request->username,
                    'tel'=>$request->tel
        ]);
        return response($user, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {   
        //Check role admin(1) member(0)
        $userAuth = auth()->user();
        if( !$userAuth->tokenCan("1") ){
            return response(["message"=>"Permission denied"], 403);
        }
        return $user->delete();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param integer $user_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateProfilePicture(Request $request, $user_id)
    {
        //Check role admin(1) or edit themselves
        $userAuth = auth()->user();
        $user = User::where('id', $user_id)->first();
        
        if( $userAuth->id != $user->id ){
            return response(
                [
                    "message"=>"Permission denied",
                    "tokenable_id" => $userAuth->id,
                    "user_id" => $user->id
                ], 403);
        }
        
        $imageData = $request->file('file');
        
        if($imageData){
            /** change file name */
            $file_name = 'user_'.time().'.'.$imageData->getClientOriginalExtension(); // $imageData->getClientOriginalExtension() is surname (.png .jpeg)

            /** set size image */
            $imgWidth = 400;
            $imgHeight = 400;

            /** public_path = /var/www/public */
            $folderThumbnailUpload = public_path('/images/users/thumbnail');
            $thumbnailPath = $folderThumbnailUpload."/".$file_name;

            /** upload into thumbnail folder */ 
            $img = Image::make($imageData->getRealPath());

            /** fit = crop and resize */ 
            $img->orientate()->fit($imgWidth, $imgHeight, function ($constraint) {
                // add callback functionality to retain maximal original image size
                $constraint->upsize();
            });
            $img->save($thumbnailPath);

            /** url = localhost or domain name */
            $newProfilePictureURL = url('/').'/images/users/thumbnail/'.$file_name;
            
        }else {
            $newProfilePictureURL = url('/').'/images/products/thumbnail/no_img.png';
        }

        // update profile picture URL in database
        $user->update( [ 'avatar'=>$newProfilePictureURL ] );

        // delete old token
        $user->tokens()->delete();

        // create sanctum token
        $token = $user->createToken($request->userAgent(), ["$user->roles"])->plainTextToken;

        // create response data
        $res = [
            'user' => $user,
            'token' => $token
        ];

        return response($res, 200);
    }

    /**
     * chage user password
     *
     * @param integer $user_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function changePassword(Request $request, $user_id)
    {
        $request->validate([
            'currentPassword'=>'required|string',
            'newPassword'=>'required|string|confirmed',
        ]);

        $userAuth = auth()->user();
        $user = User::where('id', $user_id)->first();

        /** check user_id is avilable */
        if(!$user){
            return response(["message"=>"bad request, user_id $user_id is not avilable"], 404);
        }
        
        /** Check edit themselves */
        if( $userAuth->id != $user->id  ){
            return response(["message"=>"Permission denied"], 403);
        }

        if( !Hash::check($request->currentPassword, $user->password) ){
            return response(["message"=>"Your current password is incorrect"], 401);
        }

        $encryptPassword = bcrypt($request->newPassword); // encrypt password
        /** update password in database */
        $user->update([
                    'password'=>$encryptPassword
        ]);

        return response(["message"=>"Change password is successed"], 200);
    }

    /**
     * chage user password
     *
     * @param integer $user_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function updatePersonalInfomation(Request $request, $user_id)
    {
        $userAuth = auth()->user();
        $user = User::where('id', $user_id)->first();
        
        /** check user_id is avilable */
        if(!$user){
            return response(["message"=>"bad request, user_id $user_id is not avilable"], 404);
        }
        
        /** Check edit themselves */
        if( $userAuth->id != $user->id  ){
            return response(["message"=>"Permission denied"], 403);
        }

        $request->validate([
            'fname'=>'required|string',
            'lname'=>'required|string',
            'username'=>'required|string'
        ]);

        /** update data in database */
        $user->update([
                    'fname'=>$request->fname,
                    'lname'=>$request->lname,
                    'username'=>$request->username,
                    'tel'=>$request->tel
        ]);

        /** reset token (sanctum) */ 
        $user->tokens()->delete(); // delete old token
        $token = $user->createToken($request->userAgent(), ["$user->roles"])->plainTextToken; // create sanctum token
        // create response data
        $res = [
            'user' => $user,
            'token' => $token
        ];

        return response($res, 200);
    }


    
}
