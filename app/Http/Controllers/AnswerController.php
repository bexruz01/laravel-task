<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AnswerController extends Controller
{


    public function create(Application $application)
    {

        if (!Gate::allows('update-post', auth()->user())) {
            abort(403);
        }

        return view("answers.create", ["application" => $application]);
    }

    public function store(Application $application, Request $request)
    {
        $request->validate([
            "body" => "required",
        ]);
        $application->answer()->create([
            "body" => $request->input("body"),
        ]);
        return redirect()->route("dashboard")->with("success", "Answer created");
    }
}
