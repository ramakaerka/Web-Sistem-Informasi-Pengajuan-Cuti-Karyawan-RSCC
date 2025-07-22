<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SignatureController extends Controller
{
    public function index()
    {
        return view('signature');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'signature' => 'required'
        ]);

        $image = $request->signature;
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'signatures/' . uniqid() . '.png';

        Storage::disk('public')->put($imageName, base64_decode($image));

        // Simpan ke database jika diperlukan
        // Signature::create([
        //     'name' => $request->name,
        //     'signature_path' => $imageName
        // ]);

        return redirect()->route('signature.form')
            ->with('success', 'Tanda tangan berhasil disimpan!');
    }
}