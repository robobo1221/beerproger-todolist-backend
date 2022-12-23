<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Todolist;
use Exception;
use Intervention\Image\Facades\Image;

use stdClass;

class TodolistController extends Controller
{
    private string $imagePath = "images/items";

    private function saveImage(Request $request, Todolist $item) {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . "." . $image->getClientOriginalExtension();
            $destPath = public_path($this->imagePath);

            // Make sure there is a path!
            if (!file_exists($destPath)) {
                mkdir($destPath, 0777, true);
            }

            $img = Image::make($image->getRealPath());
            $img->resize(100, 100, function($contraint) {
                $contraint->aspectRatio();
            })->save($destPath . "/" . $name);

            $item->image = $name;

            return true;
        }

        return false;
    }

    private function itemWithRealImagePath(Request $request, Todolist $item) {
        if ($item->image !== null) {
            $item->image = $request->getSchemeAndHttpHost() . "/" . $this->imagePath . "/" . $item->image;
        }

        return $item;
    }

    public function getList(Request $request) {
        $list = Todolist::all();
        $newList = array();

        if ($list) {
            foreach($list as $item) {
                $item = $this->itemWithRealImagePath($request, $item);
                array_push($newList, $item);
            }
        }

        return response()->json($list);
    }

    public function getItemById(Request $request, int $id) {
        $item = Todolist::find($id);

        if ($item === null) {
            return response()->json(new stdClass);
        }

        $newItem = $this->itemWithRealImagePath($request, $item);

        return response()->json($newItem);
    }

    public function getItemByName(Request $request, string $name) {
        $item = Todolist::where('name', $name)->first();

        if ($item === null) {
            return response()->json(new stdClass);
        }

        $item = $this->itemWithRealImagePath($request, $item);

        return response()->json($item);
    }

    public function addItem(Request $request) {
        $name = trim($request->string('name'));

        if ($name === "") {
            return response(['message' => 'Name Required!'], 400);
        }

        $item = new Todolist;
        $item->name = $request->string('name');
        $item->details = $request->string('details');

        try {
            $this->saveImage($request, $item);
        } catch (Exception $e) {
            return [$e->getMessage()];
        }

        $item->save();
        return response($item, 200);
    }

    public function deleteItemById($id) {
        $item = Todolist::find($id);

        if ($item === null) {
            return response(['message' => 'Item does not exist!'], 400);
        }

        if ($item->image) {
            try {
                unlink(public_path($this->imagePath) . "/" . $item->image);
            } catch (Exception $e) {

            }
        }

        $item->delete();

        return response($item, 200);
    }

    public function deleteItemByName($name) {
        $item = Todolist::where('name', $name)->first();

        if ($item === null) {
            return response(['message' => 'Item does not exist!'], 400);
        }

        if ($item->image) {
            try {
                unlink(public_path($this->imagePath) . "/" . $item->image);
            } catch (Exception $e) {

            }
        }

        $item->delete();

        return response($item, 200);
    }

    public function updateItem(Request $request) {
        if ($request->id === null) {
            return response(['message' => 'No id given!'], 400);
        }

        $item = Todolist::find($request->id);

        if ($item === null) {
            return response(['message' => 'Item does not exist!'], 400);
        }

        $oldItem = clone $item;

        if ($request->name !== null) {
            $name = trim($request->string('name'));

            if ($name === "") {
                return response(['message' => 'Name Required!'], 400);
            }

            $item->name = $name;
        }

        if ($request->details !== null) {
            $item->details = $request->string('details');
        }

        if ($request->completed !== null) {
            $item->completed = $request->boolean('completed');
        }

        try {
            $this->saveImage($request, $item);
        } catch (Exception $e) {
            return [$e->getMessage()];
        }

        $item->save();

        return response(["oldItem" => $oldItem, "newItem" => $item], 200);
    }
}
