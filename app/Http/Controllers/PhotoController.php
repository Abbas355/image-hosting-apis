<?php

namespace App\Http\Controllers;

use App\Http\Requests\PhotoUploadRequest;
use App\Http\Requests\StatusUpdateRequest;
use App\Models\Photo;
use App\Models\User;
use App\Models\VerifyToken;
use Illuminate\Http\Request;
use Symfony\Component\Console\Input\Input;

class PhotoController extends Controller
{
    public function upload(PhotoUploadRequest $request){
        $photo=$this->getImagePath($request);
        $ext = pathinfo($photo, PATHINFO_EXTENSION);
        $request->merge([ 'path' => $photo,'extension' => $ext ]);
        $photo=Photo::create($request->only(['title','path','extension','status']));
        $user=VerifyToken::where('token',$request->token)->first()->user;
        $photo->users()->attach($user);
        return response()->pfResponce($photo,true); 
        
    }

    public function getImagePath($request){
        $path=null;
        if ($request->hasFile('image')) {
             $path = $request->image->store('images');
        }
        return $path;
        
    }

    public function delete(Request $request){
        $user=VerifyToken::where('token',$request->token)->first()->user;
        $photo=Photo::where('id',$request->photo_id)->first();
        if($photo){
            $user->photos()->detach($request->photo_id);
            // $photo->delete();
            return response()->pfResponce('photo deleted successfully',true); 
        }
        return response()->pfResponce('photo not exist',false); 
    }


    public function list(Request $request){
        
        $photos=Photo::where('status','public')->get();
        if($photos){
            
            return response()->pfResponce($photos,true); 
        }
        return response()->pfResponce('photo not exist',false); 
    }

    public function search(Request $request)
    {
        $user=VerifyToken::where('token',$request->token)->first()->user;
        $qur = $user->photos();
        $photos=$this->searchQuery($request,$qur);
        if($photos)
        { 
            return response()->pfResponce($photos->makeHidden('pivot'),true); 
        }
        return response()->pfResponce('photo not exist',false); 
    }

    public function searchQuery($request,$qur){
        if ($request->has('title'))
        {
            $qur->where('title','like','%'.$request->get('title').'%');
        }
        if ($request->has('extension'))
        {
            $qur->where('extension', $request->get('extension'));
        }
        if ($request->has('status'))
        {
            $qur->where('status', $request->get('status'));
        }
        if ($request->has('date'))
        {
            $qur->whereDate('created_at', $request->get('date'));
        }
        if ($request->has('time'))
        {
            $qur->whereTime('created_at', $request->get('time'));
        }
        return $qur->orderBy('id')->get();

    }

    public function statusUpdate(StatusUpdateRequest $request){
        $photo=Photo::where('id',$request->photo_id)->first();
        if(hash_equals($request->status,"private")){
            if($request->has('email')){
                $user=User::where('email',$request->email)->first();
                $photo->users()->attach($user);
            }else{
                return response()->pfResponce('shered email must be given for private photot ',false); 
            }
            
        }
        $photo->status=$request->status;
        $photo->save();
        return response()->pfResponce($photo,true); 
    }

    public function getShareableLink(Request $request){
        $photo=Photo::where('id',$request->photo_id)->first();
        if(!hash_equals($photo->status,"public")){
            $user=VerifyToken::where('token',$request->token)->first()->user;
            if(!$photo->users->contains($user)){
                return response()->pfResponce("not accessible for you",false); 
            }
        }
        $imageLink=route('photview.photos', [$photo->id]);
        return response()->pfResponce($imageLink,true); 
    }

    public function photview(Request $request,$id){
        $photo=Photo::where('id',$id)->first();
        if(!hash_equals($photo->status,"public")){
            $user=VerifyToken::where('token',$request->token)->first()->user;
            if(!$photo->users->contains($user)){
                return response()->pfResponce("not accessible for you",false); 
            }
        }
        return response()->pfResponce($photo->makeHidden('users'),true); 
    }
}
