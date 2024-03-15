<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualityFormRequest;
use App\Http\Requests\CoverUpdateRequest;
use App\Http\Resources\ActualityCollection;
use App\Models\Actuality;
use App\Models\Category;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ActualityController extends Controller
{

    public function index()
    {
        return view('actualities.index', ['actualities' => Actuality::paginate(25)]);
        // $this->success(Actuality::all()->orderBy('created_at' , 'desc')->paginate(25))
    }


    public function create()
    {
        $actuality = new Actuality();
        $categories = Category::all();
        return view('actualities.form', ['actuality' => $actuality, 'categories' => $categories]);
    }


    public function store(ActualityFormRequest $request)
    {

        try {
            $validatedData = $request->validated();

            $cover = Gallery::create(['path' => $this->upload_file($request, 'cover')]);

            $validatedData['cover_id'] = $cover->id;

            unset($validatedData['cover']);

            $actuality = Actuality::create($validatedData);

            return redirect()->route('actuality.index')->with('success', "L'actualité a bien été créée !");
        } catch (\Exception $e) {

            dd($e->getMessage());
        }
    }

    public function edit(Actuality $actuality)
    {
        $categories = Category::all();

        return view('actualities.form', ['actuality' => $actuality, 'categories' => $categories]);
    }

    public function show(Actuality $actuality)
    {
        //dd(url('storage/' . $actuality->cover->path));

        // $actuality = Actuality::find($id);

        // dd($actuality->title);

        return $this->success(new ActualityCollection($actuality));
    }

    public function update(CoverUpdateRequest $request, Actuality $actuality)
    {

        $data = $request->validated();

        if ($request->hasFile('cover')) {

            Storage::delete("public/actualities/covers/$actuality->path");
            $cover = Gallery::find($actuality->cover_id);

            $cover->update(['path' => $this->upload_file($request, 'cover')]);

            $data['cover_id'] = $cover->id;

        }

        unset($data['cover']);

        $actuality->update($data);

        return redirect()->route('actuality.index')->with('success', "L'actualité à bien été modifier !");
    }

    public function destroy(Actuality $actuality)
    {
        $actuality->delete();
        return redirect()->route('actualities.index')->with('success', "L'actualité à été bien supprimmer !");
    }
}
