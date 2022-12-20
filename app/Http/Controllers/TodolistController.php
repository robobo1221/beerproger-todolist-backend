<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Todolist;
use Intervention\Image\Facades\Image;

use stdClass;

class TodolistController extends Controller
{
    private string $imagePath = "images/items/";

    private function saveImage(Request $request, Todolist $item) {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . "." . $image->getClientOriginalExtension();
            $destPath = public_path($this->imagePath);

            $img = Image::make($image->getRealPath());
            $img->resize(100, 100, function($contraint) {
                $contraint->aspectRatio();
            })->save($destPath . "/" . $name);

            $destPath = public_path("/");

            $image->move($destPath, $name);
            $item->image = $name;
        }
    }

    public function getList() {
        return response()->json(Todolist::all());
    }

    public function getItemById(int $id) {
        $item = Todolist::find($id);

        if ($item === null) {
            return response()->json(new stdClass);
        }

        return response()->json($item);
    }

    public function getItemByName(string $name) {
        $item = Todolist::where('name', $name)->first();

        if ($item === null) {
            return response()->json(new stdClass);
        }

        return response()->json($item);
    }

    public function addItem(Request $request) {
        $name = trim($request->string('name'));

        if ($name === "") {
            return response('Error: Name Required!', 400);
        }

        $item = new Todolist;
        $item->name = $request->string('name');
        $item->details = $request->string('details');

        $this->saveImage($request, $item);

        $item->save();
        return response($item, 200);
    }

    public function deleteItemById($id) {
        $item = Todolist::find($id);

        if ($item === null) {
            return response('Error: Item does not exist!', 400);
        }

        $item->delete();

        return response($item, 200);
    }

    public function deleteItemByName($name) {
        $item = Todolist::where('name', $name)->first();

        if ($item === null) {
            return response('Error: Item does not exist!', 400);
        }

        $item->delete();

        return response($item, 200);
    }

    public function updateItem(Request $request) {
        if ($request->id === null) {
            return response('Error: No id given!', 400);
        }

        $item = Todolist::find($request->id);

        if ($item === null) {
            return response('Error: Item does not exist!', 400);
        }

        $oldItem = clone $item;

        if ($request->name !== null) {
            if ($request->name === $item->name) {
                return response('Error: New name cannot be the same as the previous!', 400);
            }

            $name = trim($request->string('name'));

            if ($name === "") {
                return response('Error: Name Required!', 400);
            }

            $item->name = $name;
        }

        if ($request->details !== null) {
            $item->details = $request->string('details');
        }

        if ($request->completed !== null) {
            $item->completed = $request->boolean('completed');
        }

        $this->saveImage($request, $item);

        $item->save();

        return response(["oldItem" => $oldItem, "newItem" => $item], 200);
    }
}
