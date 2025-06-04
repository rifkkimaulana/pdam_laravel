<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staf;

class StafController extends Controller
{

    public function index()
    {
        $staf = Staf::with(['pengelola', 'user'])->get();

        $staf->transform(function ($item) {
            $item->user_pengelola = $item->pengelola ? $item->pengelola->user : null;
            return $item;
        });

        return response()->json($staf);
    }


    public function show($id)
    {
        return response()->json(Staf::with('pengelola', 'user')->findOrFail($id));
    }

    public function store(Request $request) {}


    public function update(Request $request, $id) {}


    public function destroy($id)
    {
        Staf::destroy($id);
        return response()->json(['message' => 'Staf berhasil dihapus']);
    }
}
