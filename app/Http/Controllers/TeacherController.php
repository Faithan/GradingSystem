<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeacherController extends Controller
{
    //
  public function teacher(){
    return view("teacher.teacher_dashboard");
  }
}
