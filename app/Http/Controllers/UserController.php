<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::where('id','!=',auth()->id())->withCount(['unreadMessage'])->get();
        return view('dashboard',compact('users'));
    }

    public function userChat(int $userId){
        return view('user-chat',compact('userId'));
    }

}
