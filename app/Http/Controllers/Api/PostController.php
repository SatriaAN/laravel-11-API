<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    // menampilkan semua data posts
    public function index() {
        $posts = Post::latest()->paginate(5);

        //mengembalikan nilai sebagai resource
        return new PostResource(true, 'List Data Posts', $posts);
    }

    public function store(Request $request) {

        // mendefinisikan rules validasi
        $validator = Validator::make($request->all(),[
            'image'=> 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'=> 'required',
            'content'=> 'required',
        ]);

        // cek jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // upload image
        $image = $request->file('image');
        $image->storeAs('public/post', $image->hashName());

        // create post
        $post = Post::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'content'   => $request->content,
        ]);

        // return response
        return new PostResource(true,'Data Post Berhasil Ditambahkan!', $post);
    }

    public function show($id) {
        $post = Post::find($id);
        return new PostResource(true,'Detail Data Post', $post);
    }

    public function update(Request $request, $id)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'title'     => 'required',
            'content'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find post by ID
        $post = Post::find($id);

        //check if image is not empty
        if ($request->hasFile('image')) {

            //upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //delete old image
            Storage::delete('public/posts/' . basename($post->image));

            //update post with new image
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        } else {

            //update post without image
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        }

        //return response
        return new PostResource(true, 'Data Post Berhasil Diubah!', $post);
    }

    public function destroy($id) {
        // mencaru id
        $post = Post::find($id);
        // menghapus gambar
        Storage::delete('public/posts/' . basename($post->image));
        // menghapus post
        $post->delete();

        return new PostResource(true,'Data Berhasil Dihapus', null);
    }
}