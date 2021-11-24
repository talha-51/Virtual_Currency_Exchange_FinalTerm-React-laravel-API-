<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\EditProfileRequest;
use App\Http\Requests\EditUserInfoRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

class AdminHomeController extends Controller
{
    
    public function index(Request $req){
        //$id = $req->session()->get('id');
        //$name = DB::table('users')->where('id', $id)->first();
        
        $orders = DB::table('orders')->count();
        $values = DB::table('orders')->sum('price_on_selling_time');
        $counter = DB::table('site_infos')->value('trafic_number');
        $users = DB::table('users')->where('status', '<>', 'deleted')->count();
        $admins = DB::table('users')->where('type', 'admin')->where('status', '<>', 'deleted')->count();
        $sellers = DB::table('users')->where('type', 'seller')->where('status', '<>', 'deleted')->count();
        $buyers = DB::table('users')->where('type', 'buyer')->where('status', '<>', 'deleted')->count();
        $primes = DB::table('users')->where('prime_status', 'prime')->count();

        //return view('admin.adminHome', compact('users','admins','sellers','buyers','primes','orders','values','counter'))->with('adminHome',$name);

        return response()->json([
            'orders' => $orders,
            'values' => $values,
            'counter' => $counter,
            'users' => $users,
            'admins' => $admins,
            'sellers' => $sellers,
            'buyers' => $buyers,
            'primes'=>$primes
        ]);
    }

    public function addAdmin(Request $req){
        return response()->json([
            'success' => true
        ]);
    }

    public function verifyAddAdmin(Request $req){

        $user= $req->user();

        DB::table('users')->insert(
            ['name' => $req->name,
            'email' => $req->email,
            'password' => $req->password,
            'address' => $req->address,
            'phone_number' => $req->phone_number,
            'nid_number' => $req->nid_number,
            'type' => 'admin',
            'aproved_by' => $user->id,
            'status' => 'active',
            'created_at' => date('Y/m/d H:i:s'),
        ]);
        return response()->json([
            'success' => true
        ]);
    }

    public function editProfile(Request $req){

        $user= $req->user();
        $profDetails = DB::table('users')->where('id', $user->id)->first();

        return response()->json([
            'profDetails' => $profDetails,
            'id' => $user->id
        ]);
    }

    public function verifyEditProfile(Request $req, $id){


        $validator = Validator::make($req->all(), [
            'name' => 'required|max:20|min:4',
            'email' => 'required|max:20|min:11',
            'password' => 'required|max:20|min:8|regex:/^.+@.+$/',
            'address' => 'required|max:50|min:3',
            'phone_number' => 'required|max:11|min:11',
         ],
         [
            'name.required' => 'Must provide a name',
            'email.required' => 'Must provide a email.',
            'password.required' => 'Must provide a password within 8-20 Characters',
            'password.regex' => 'Password must have atleast one of these characters: "/^.+@.+$/"'
        ]
        );

         if ($validator->fails()) {
            return response()->json([
                "errorData"=>$validator->errors(),
                'msg' => "Validation Error",
                'status' => 'error',
                'error'=>'400'
            ]);
        }


        if($req->hasFile('profile_picture')){
            $extension = $req->profile_picture->getClientOriginalExtension();
            $newName = 'adminDP1'.'.'.$extension;
            $folderPath = "admin/";
            DB::table('users')
            ->where('id', $id)
            ->update(['name' => $req->name,
                    'email' => $req->email,
                    'password' => $req->password,
                    'address' => $req->address,
                    'phone_number' => $req->phone_number,
                    'profile_picture' => $folderPath.$newName,
                    ]);
           
            $req->profile_picture->move($folderPath, $newName);
        }


        else{
            DB::table('users')
            ->where('id', $id)
            ->update(['name' => $req->name,
                     'email' => $req->email,
                     'password' => $req->password,
                     'address' => $req->address,
                     'phone_number' => $req->phone_number,
                      'updated_at' => date('Y/m/d H:i:s'),
                    ]);
        }
        return response()->json([
            'status' => $req->name
        ]);
    }

    public function viewAllUserInfo(Request $req){
        $users = DB::table('users')->where('status', '<>', 'deleted')->get();

        return response()->json([
            'users' => $users
        ]);
    }

    public function editUserInfo(Request $req, $id){
        $users = DB::table('users')->where('id', $id)->first();

        return response()->json([
            'users' => $users
        ]);
    }
    
    public function verifyEditUserInfo(Request $req, $id){

        $validator = Validator::make($req->all(), [
            'name' => 'required|max:20|min:4',
            'email' => 'required|max:20|min:11',
            'password' => 'required|max:20|min:8|regex:/^.+@.+$/',
            'address' => 'required|max:50|min:3',
            'phone_number' => 'required|max:11|min:11',
            'prime_status' => 'required',
            'status' => 'required',
         ],
         [
            'name.required' => 'Must provide a name',
            'email.required' => 'Must provide a email.',
            'password.required' => 'Must provide a password within 8-20 Characters',
            'password.regex' => 'Password must have atleast one of these characters: "/^.+@.+$/"',
            'prime_status.required' => 'Must choose one',
            'status.required' => 'Must choose one'
        ]
        );

         if ($validator->fails()) {
            return response()->json([
                "errorData"=>$validator->errors(),
                'msg' => "Validation Error",
                'status' => 'error',
                'error'=>'400'
            ]);
        }

        DB::table('users')
            ->where('id', $req->id)
            ->update(['name' => $req->name,
                      'email' => $req->email,
                      'password' => $req->password,
                      'address' => $req->address,
                      'phone_number' => $req->phone_number,
                      'prime_status' => $req->prime_status,
                      'status' => $req->status,
                      'updated_at' => date('Y/m/d H:i:s'),
                    ]);
        return response()->json([
            'status' => "success"
        ]);
    }

    public function deleteUserInfo(Request $req, $id){
        DB::table('users')
        ->where('id', $req->id)
        ->update(['status' => 'deleted']);
        
        return response()->json([
            'status' => "success"
        ]);
    }

    public function viewAllTransaction(Request $req){
        $orders = DB::table('orders')->get();

        return response()->json([
            'orders' => $orders
        ]);
    }

    public function userReports(Request $req){
        $reports = DB::table('reports')->orderBy('rep_id','desc')->get();

        return response()->json([
            'reports' => $reports
        ]);
    }

    public function announcement(Request $req){
        $announcements = DB::table('announcements')->where('status','=','active')->orderBy('ann_id','desc')->get();

        return response()->json([
            'announcements' => $announcements
        ]);
    }

    public function sendAnnouncement(Request $req){

        $user= $req->user();

        DB::table('announcements')->insert(
            ['admin_id' => $user->id,
            'description' => $req->desc,
            'created_at' => date('Y/m/d H:i:s'),
            'status' => 'active'
        ]);
        return response()->json([
            'status' => "success"
        ]);
    }

    public function deleteAnnouncement(Request $req, $id){
        DB::table('announcements')
        ->where('ann_id', $req->id)
        ->update(['status' => 'deleted']);
        
        return response()->json([
            'status' => "success"
        ]);
    }

    public function prime_approval(Request $req){
        $prime = DB::table('payments')->get();

        return response()->json([
            'prime' => $prime
        ]);
    }

    public function editPrimeDuration(Request $req, $seller_id){
        $users = DB::table('prime_resets')->where('seller_id', $seller_id)->first();

        return response()->json([
            'users' => $users
        ]);
    }

    public function updatePrimeDuration(Request $req, $seller_id){
        DB::table('prime_resets')
            ->where('seller_id', $req->seller_id)
            ->update(['prime_expire_date' => $req->prime_expire_date,
                      'updated_at' => date('Y/m/d H:i:s'),
                    ]);

        return response()->json([
            'status' => "success"
        ]);
    }
}
